<?php

namespace App\Counselling\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Helpers\StatusHelper;
use App\Helpers\DateHelper;
use App\Helpers\GenerateMessageHelper;
use App\Counselling\Models\CounsellingSetup;
use App\Counselling\Models\Counselling;
use App\Counselling\Models\CounsellingMessage;
use App\Persona\Models\CounsellingField;
use App\Persona\Models\Persona;
use App\Course\Models\CourseMember;
use App\Course\Models\Course;

class CounsellingController extends Controller
{
    /** Show the counselling view based on the chat's status
     *
     * @param  \App\Counselling\Models\Counselling  $counselling
     */
    public function index(Counselling $counselling) {
        $this->authorize('userIsCounsellingCreator', $counselling);
        $this->storeCourseInSession($counselling->counsellingSetup->course->id);
        $course = Course::find($counselling->counsellingSetup->course->id);
        if (StatusHelper::getStatusById($counselling->status_chat_id) === 'in progress') {
            return view('student.chat', ['counsellingId' => $counselling->id, 'end_date' => DateHelper::formatDate($course->end_date)]);
        } else {
            return view('student.postprocessing', ['counsellingId' => $counselling->id, 'end_date' => DateHelper::formatDate($course->end_date)]);
        }
    }

    /** Get data for given counselling
     *
     * @param  \App\Counselling\Models\Counselling  $counselling
     * @return \Illuminate\Http\Response
     */
    public function indexData(Counselling $counselling) {
        $this->authorize('userIsCounsellingCreator', $counselling);
        $counselling->load('counsellingMessages');
        return response()->json(['counselling' => $this->adaptCounsellingProperties($counselling)], 200);
    }

    /** Get all counsellings for a user for given setup-id
     *
     * @param  \App\Counselling\Models\CounsellingSetup  $counsellingSetup
     * @return \Illuminate\Http\Response
     */
    public function getCounsellingBySetupId(CounsellingSetup $counsellingSetup)
    {
        $userId = Auth::id();

        $counsellings = Counselling::where('counselling_setup_id', $counsellingSetup->id)
            ->whereHas('courseMember', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($counselling) {
                $counselling = $this->adaptCounsellingProperties($counselling);
                return $counselling;
            });

        return response()->json($counsellings);
    }

    /** Show wizard for exercise creation
     *
     * @param  \App\Counselling\Models\CounsellingSetup  $counsellingSetup
     */
    public function showWizard(CounsellingSetup $counsellingSetup) {
        $this->checkEditRights($counsellingSetup->course_id);

        $settings = $counsellingSetup->settings;
        $counsellingFields = CounsellingField::whereIn('id', $settings['counselling_fields'])->select('id', 'name')->get();
        $personae = Persona::whereIn('id', $settings['personae'])->select('id', 'name', 'counselling_field_id')->get();
        $data =  [
            'counselling_fields' => $counsellingFields,
            'personae' => $personae,
            'setup_id' => $counsellingSetup->id
        ];
        $this->storeCourseInSession($counsellingSetup->course->id);
        return view('student.wizard', ['settings' => $data]);
    }
    
    /** Create new counselling
     *
     * @param  \App\Counselling\Models\CounsellingSetup  $counsellingSetup
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, CounsellingSetup $counsellingSetup) {
        $this->checkEditRights($counsellingSetup->course_id);

        if ($counsellingSetup->mandatory || !$request->filled('persona')) { // load possible personae from database, if it's a task or no personae are given
            $personae = Persona::whereIn('id', $counsellingSetup->settings['personae'])->pluck('id')->toArray();
        } else {
            $personae = $request->input('persona');
        }

        if (count($personae) > 1) { // select random persona_id, if more than one is possible
            $index = array_rand($personae);
            $personaId = $personae[$index];
        } else {
            $personaId = reset($personae);
        }
    
        $data =  [
            'persona_id' => $personaId,
            'counselling_setup_id' => $counsellingSetup->id,
            'course_member_id' => CourseMember::where('user_id', auth()->user()->id)->where('course_id', $counsellingSetup->course->id)->first()->id,
            'title' => Persona::find($personaId)->name . ' - ' . Carbon::now()->format('d.m.Y'),
            'status_chat_id' => StatusHelper::getIdFromTitle('in progress')
        ];

        $counselling = Counselling::create($data);
        return response()->json($counselling);
    }

    /** Delete the given counselling
     *
     * @param  \App\Counselling\Models\Counselling  $counselling
     * @return \Illuminate\Http\Response
     */
    public function delete(Counselling $counselling) {
        $this->authorize('userIsCounsellingCreator', $counselling);

        Counselling::destroy($counselling->id);
        return response()->json(['message' => 'Beratung gelöscht'], 200);
    }

    /** Change the chat status to 'done'
     *
     * @param  \App\Counselling\Models\Counselling  $counselling
     * @return \Illuminate\Http\Response
     */
    public function finishChat(Counselling $counselling) {
        $this->authorize('userIsCounsellingCreator', $counselling);

        $courseMember = CourseMember::find($counselling->course_member_id);
        $this->checkEditRights($courseMember->course_id);
        $counselling->status_chat_id = StatusHelper::getIdFromTitle('done');
        $counselling->save();
        return response()->json($counselling);
    }

    /** Edit given counselling
     *
     * @param  \App\Counselling\Models\Counselling  $counselling
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Counselling $counselling) {
        $this->authorize('userIsCounsellingCreator', $counselling);
        $courseMember = CourseMember::find($counselling->course_member_id);
        $this->checkEditRights($courseMember->course_id);
        $data = $request->only(
            'title',
            'note',
        );
        if (array_key_exists('title', $data)) {
            $counselling->title = $data['title'];
        }
        if (array_key_exists('note', $data)) {
            $counselling->note = $data['note'];
        }
        $counselling->save();
        $counselling->load('counsellingMessages');
        return response()->json(['message' => 'Erfoglreich gespeichert', 'counselling' => $this->adaptCounsellingProperties($counselling)], 200);
    }

    /** Stores new message from user
     * 
     * @param  \App\Counselling\Models\Counselling  $counselling
     * @return \Illuminate\Http\Response
    */
    public function storeMessage(Request $request, Counselling $counselling) {
        $this->authorize('userIsCounsellingCreator', $counselling);
        $courseMember = CourseMember::find($counselling->course_member_id);
        $this->checkEditRights($courseMember->course_id);

        $textUser = '';
        if ($request->has('message')) {
            $textUser = $request->input('message');
            $messageUser = new CounsellingMessage();
            $messageUser->counselling_id = $counselling->id;
            $messageUser->content = $textUser;
            $messageUser->author = 'user';
            $messageUser->save();
            $response[] = $messageUser;
            return response()->json($response);
        }
        return response()->json(['message' => 'Nachricht konnte nicht gesendet werden.'], 500);
    }

    /** Generates new Message
     *
     * @param  \App\Counselling\Models\Counselling  $counselling
     * @return \Illuminate\Http\Response
     */
    public function generate(Counselling $counselling) {
        $this->authorize('userIsCounsellingCreator', $counselling);
        $courseMember = CourseMember::find($counselling->course_member_id);
        $this->checkEditRights($courseMember->course_id);

        $response = [];
        try {
            $messageViKl = GenerateMessageHelper::generateWithOpenAi($counselling->persona_id, $counselling->id);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Fehler beim Generieren einer neuen Nachricht.'], 500);
        }
        $message = new CounsellingMessage();
        $message->counselling_id = $counselling->id;
        $message->content = $messageViKl;
        // $message->content = 'Hallo';
        $message->author = 'vikl';
        $message->save();
        $response[] = $message;
    
        sleep(3);

        return response()->json($response);
    }

    /** Handles edit request for counsellingMessage note
     *
     * @param  \App\Counselling\Models\Counselling  $counselling
     * @return \Illuminate\Http\Response
     */
    public function editMessageNote(Request $request, $counsellingId, $messageNumber) {
        $counsellingMessage = CounsellingMessage::where(['counselling_id' => $counsellingId, 'message_number' => $messageNumber])->first();
        if ($counsellingMessage) {
            $text = $request->input('text');
            $additions = $counsellingMessage->additions;
            $additions['note'] = $text;
            $counsellingMessage->additions = $additions;
            $counsellingMessage->save();

            return response()->json(['message' => 'Text erfolgreich gespeichert.', 'counsellingMessage' => $counsellingMessage], 200);
        }
        return response()->json(['message' => 'Nachricht nicht gefunden'], 500);
    }

    private function adaptCounsellingProperties($counselling) {
        // TODO_FB: feedback or peer-review exists -> translate status_id
        $persona = Persona::find($counselling->persona_id);
        $counselling->persona = $persona->name;
        $counselling->counselling_field = CounsellingField::find($persona->counselling_field_id)->name;
        $counselling->start_date = DateHelper::formatDate($counselling->created_at);
        $counselling->status_chat = StatusHelper::getStatusById($counselling->status_chat_id);
        $counselling->course = $counselling->counsellingSetup->course->id;

        return $counselling;
    }

    private function storeCourseInSession($courseId) {
        $course = Course::find($courseId);
        session(['current_course' => [
            'id' => $courseId,
            'name' => $course->name,
        ]]);
    }

    private function checkEditRights($course_id) {
        $course = Course::find($course_id);
        $this->authorize('userIsCourseMember', $course);
        $this->authorize('courseIsEditable', $course);
    }
}
