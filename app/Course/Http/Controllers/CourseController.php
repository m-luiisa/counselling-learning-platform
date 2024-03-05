<?php

namespace App\Course\Http\Controllers;

use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Course\Models\Course;
use App\Course\Models\CourseMember;
use App\Counselling\Models\CounsellingSetup;
use App\Counselling\Models\Counselling;
use App\Models\Role;
use App\Persona\Models\Persona;
use App\Helpers\RoleHelper;
use App\Helpers\DateHelper;

class CourseController extends Controller
{
    /** Displays the course overview for students, displays course settings for editingteacher
     * Depending on role of course-member
     *
     * @param  \App\Course\Models\Course  $course
     */
    public function index(Course $course)
    {
        $this->authorize('userIsCourseMember', $course);
        $this->storeCourseInSession($course->id, $course->name);

        $user = auth()->user();
        $courseMember = $user->courseMembers()->where('course_id', $course->id)->first();
        if ($courseMember->role->title === 'student') {
            $data = $course->load('counsellingSetups');
            $data->end_date = DateHelper::formatDate($course->end_date);
            $data->start_date = DateHelper::formatDate($course->start_date);
            $data->counsellingSetups = $this->setTaskPersonaForFE($data->counsellingSetups);
            $data->pseudonym = $this->getPseudonymforCurrentUser($course->id);
            return view('student.course',  ['course' => $data]);
        } else if ($courseMember->role->title === 'editingteacher') {
            return view('teacher.course-settings', ['course_id' => $course->id]);
        }
    }

    /** Returns information for given course
     *
     * @param  \App\Course\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function indexData(Course $course) {
        $this->authorize('userIsCourseMember', $course);

        $data = $course->load('counsellingSetups');
        $data->end_date = DateHelper::formatDate($course->end_date);
        $data->start_date = DateHelper::formatDate($course->start_date);
        return response()->json($data);
    }

    /** Displays the course creation view for teachers.
     */
    public function create() {
        return view('teacher.course-settings', ['course' => null]);
    }

    /** Handles the creation or editing of a course, including related counselling_setups
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $this->validate($request, [
            'name'              => 'required',
            'enrollment_key'    => 'required',
            'start_date'        => 'required',
            'end_date'          => 'required',
            'counselling_setups'=> 'required|array|min:1', // ensure that at least one setup exists
            'counselling_setups.*.settings.personae' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    // Check if there's at least one persona ID
                    if (empty($value)) {
                        $fail("Mindestens eine Persona für Übungen und jede Aufgabe notwendig.");
                    } else {
                        // Check if all persona IDs exist in the database and are enabled
                        $personaIds = collect($value)->unique();
                        $existingPersonas = Persona::whereIn('id', $personaIds)->where('enabled', true)->count();
                        if ($personaIds->count() !== $existingPersonas) {
                            $fail("Ausgewählte Persona nicht in der Datenbank oder deaktiviert.");
                        }
                    }
                },
            ],
        ]);

        if (!$request->input('counselling_setups') || !collect($request->input('counselling_setups'))->contains('mandatory', false)) {
            return response()->json(['message' => 'Beratungs-Setting für Übungen fehlt'], 400);
        }

        $data = $request->only(
            'name',
            'enrollment_key',
            'start_date',
            'end_date',
            'counselling_setups'
        );
        $course_id = $request->only('id');
    
        // Create new course
        if ($course_id == []) {
            $existingCourse = Course::where('name', $data['name'])->first();
            if ($existingCourse) {
                return response()->json(['message' => 'Ein Kurs mit diesem Namen existiert bereits.'], 400);
            }

            $data['creator_id'] = auth()->user()->id;
            $course = Course::create($data);

            $course_member = CourseMember::create([
                'course_id' => $course->id,
                'user_id' => auth()->user()->id,
                'role_id' => RoleHelper::getIdFromTitle('editingteacher'),
            ]);

            foreach ($data['counselling_setups'] as $setup_data) {
                $mandatory = $setup_data['mandatory'] ?? false;
                $dueDate = $setup_data['due_date'] ?? null;
                $settings = $setup_data['settings'];

                $counsellingSetup = CounsellingSetup::create([
                    'mandatory' => $mandatory,
                    'due_date' => $dueDate,
                    'course_id' => $course->id,
                    'settings' => $settings
                ]);
            }
            
            return response()->json(['message' => 'Kurs erfolgreich erstellt.', 'id' => $course->id], 200);
        } 
        // edit existing course
        else {
            $course = Course::find($course_id)->first();
            $this->authorize('userIsEditingTeacher', $course);
            $this->authorize('courseIsEditable', $course);

            // edit base data
            $course->name = $data['name'];
            $course->start_date = $data['start_date'];
            $course->end_date = $data['end_date'];
            $course->enrollment_key = $data['enrollment_key'];
            $course->save();

            // edit counselling setups: overwrite existing one or create one
            foreach ($data['counselling_setups'] as $setup_data) {
                $mandatory = $setup_data['mandatory'] ?? false;
                $dueDate = $setup_data['due_date'] ?? null;
                $settings = $setup_data['settings'];

                $existingSetup = CounsellingSetup::find($setup_data['id']);

                if ($existingSetup) {
                    // update data
                    $existingSetup->update([
                        'mandatory' => $mandatory,
                        'due_date' => $dueDate,
                        'settings' => $settings,
                    ]);
                } else {
                    // create new setup
                    $newSetup = CounsellingSetup::create([
                        'mandatory' => $mandatory,
                        'due_date' => $dueDate,
                        'settings' => $settings,
                        'course_id' => $course->id,
                    ]);
                }
            }
            // delete counselling setups from the course which are not in request-data
            $ids = collect($data['counselling_setups'])->pluck('id')->all();
            DB::table('counselling_setups')
                ->where('course_id', $course->id)
                ->whereNotIn('id', $ids)
                ->delete();

            $course->load('counsellingSetups');
            return response()->json(['message' => 'Kurs aktualisiert.', 'course' => $course], 200);
        }

    }

    /** Deletes given course
     *
     * @param  \App\Course\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function delete(Course $course) {
        $this->authorize('userIsEditingTeacher', $course);

        if (!$course) {
            return response()->json(['message' => 'Kurs nicht gefunden'], 404);
        }
        $course->delete();
        return response()->json(['message' => 'Kurs gelöscht'], 200);
    }


    /** Retrieves a list of available roles from database
     *
     * @return \Illuminate\Http\Response
     */
    public function getRoles() {
        $roles = Role::where('title', '!=', 'admin')
            ->select('id', 'title')
            ->orderBy('id', 'asc')
            ->get();
        $roles->each(function ($role) {
            $role->text = RoleHelper::translateRole($role->id);
        });
        return response()->json($roles);
    }

    /** Displays the exercises view for students
     *
     * @param  \App\Course\Models\Course  $course
     */
    public function getExercisesView(Course $course) {
        $this->authorize('userIsCourseMember', $course);

        $exerciseSetup = $course->exerciseSetup->first();
        $this->storeCourseInSession($course->id, $course->name);
        return view('student.exercises', ['id' => $exerciseSetup->id, 'end_date' => DateHelper::formatDate($course->end_date)]);
    }

    /** Displays the tasks view for students
     *
     * @param  \App\Course\Models\Course  $course
     */
    public function getTasksView(Course $course) {
        $this->authorize('userIsCourseMember', $course);

        $tasksSetups = $course->tasksSetups;
        $tasksSetups = $this->setTaskPersonaForFE($tasksSetups);
        $this->storeCourseInSession($course->id, $course->name);
        return view('student.tasks', ['setups' => $tasksSetups, 'end_date' => DateHelper::formatDate($course->end_date)]);
    }

    /** Retrieves the pseudonym for the current user in the course
     *
     * @param  \App\Course\Models\Course  $course
     * @return \Illuminate\Http\Response
     */

    public function getPseudonym(Course $course) {
        $this->authorize('userIsCourseMember', $course);

        $responseData = $this->getPseudonymforCurrentUser($course->id);
        if ($responseData) {
            return response()->json($responseData);
        } else {
            return response()->json(['message' => 'Kursmitglied nicht gefunden'], 404);
        }
    }

    /** Retrieves the statistics for the current user in the course
     *
     * @param  \App\Course\Models\Course  $course
     * @return \Illuminate\Http\Response
     */

     public function getStatistics(Course $course) {
        $this->authorize('userIsCourseMember', $course);

        $courseMember = CourseMember::where('course_id', $course->id)->where('user_id', auth()->user()->id)->first();
        if ($courseMember) {
            // load all data
            $personae = Persona::where('enabled', true)->get();

            $countAllData = Counselling::whereHas('counsellingSetup', function ($query) use ($course) {
                    $query->where('course_id', $course->id);
                })
                ->select('persona_id', \DB::raw('COUNT(*) as countAll'))
                ->groupBy('persona_id')
                ->get();
            
            $countUserData = Counselling::where('course_member_id', $courseMember->id)
                ->whereHas('counsellingSetup', function ($query) use ($course) {
                    $query->where('course_id', $course->id);
                })
                ->select('persona_id', \DB::raw('COUNT(*) as countUser'))
                ->groupBy('persona_id')
                ->get();
            
            $countCourseMembers = CourseMember::where('course_id', $course->id)
                ->where('role_id', RoleHelper::getIdFromTitle('student'))
                ->count();
            $statistics = [];
        
            // calculate statistic for each persona
            foreach ($personae as $persona) {
                $countAll = $countAllData->where('persona_id', $persona->id)->first()->countAll ?? 0;
                $countUser = $countUserData->where('persona_id', $persona->id)->first()->countUser ?? 0;
        
                $averageUsers = $countAll > 0 ? round($countAll / $countCourseMembers, 0) : 0;
        
                array_push($statistics, [
                    'id' => $persona->id,
                    'name' => $persona->name,
                    'counsellingField' => $persona->counsellingField->name,
                    'countAll' => $countAll,
                    'countUser' => $countUser,
                    'averageUser' => $averageUsers,
                ]);
            }
        
            return response()->json(['statistics' => $statistics, 'countCourseMembers' => $countCourseMembers], 200);
        } else {
            return response()->json(['message' => 'Kursmitglied nicht gefunden'], 404);
        }
    }



    /** Formats task data for the front end
     */
    private function setTaskPersonaForFE($setups) {
        $setups->where('mandatory', 'true')->each(function ($task) {
            $task->due_date = DateHelper::formatDate($task->due_date);

            // set persona and counselling field for mandatory = true
            $personaIds = $task->settings['personae'];

            $personaeData = Persona::with('counsellingField:id,name')
                ->select('id', 'name', 'counselling_field_id')
                ->whereIn('id', $personaIds)
                ->get();
            
            $task->personae = $personaeData->map(function ($persona) {
                return [
                    'id' => $persona->id,
                    'name' => $persona->name,
                    'counselling_field' => $persona->counsellingField->name,
                ];
            });
            
            unset($task->settings);
        });
        return $setups;
    }

    /** Stores course information in the session
     */
    private function storeCourseInSession($courseId, $courseName) {
        session(['current_course' => [
            'id' => $courseId,
            'name' => $courseName,
        ]]);
    }

    /** Retrieves the pseudonym for the current user in the course if available.
     */
    private function getPseudonymforCurrentUser($courseId) {
        $courseMember = CourseMember::where('course_id', $courseId)
            ->where('user_id', Auth::id())
            ->first();
        
        if ($courseMember) {
            $pseudoFirstName = $courseMember->pseudo_first_name ?? 'Unbekannt';
            $pseudoLastName = $courseMember->pseudo_last_name ?? 'Unbekannt';
            
            return [
                'pseudo_first_name' => $pseudoFirstName,
                'pseudo_last_name' => $pseudoLastName,
            ];
        } else {
            return false;
        }
    }

}
