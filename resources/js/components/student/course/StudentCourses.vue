<template>
    <div>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 30%;">Kursname</th>
                    <th style="width: 15%;" class="d-none d-sm-table-cell">Kursstart</th>
                    <th style="width: 15%;" class="d-none d-sm-table-cell">Kursende</th>
                    <th style="width: 15%">Aufgaben</th>
                    <th style="width: 1%"></th>
                    <th style="width: 1%"></th>
                </tr>
            </thead>
            <tbody v-if="courses.length > 0">
                <tr v-for="course in courses" :key="course.id">
                    <td>{{ course.name }}</td>
                    <td class="d-none d-sm-table-cell">{{ new Date(course.start_date).toLocaleDateString("de") }}</td>
                    <td class="d-none d-sm-table-cell">{{ new Date(course.end_date).toLocaleDateString("de") }}</td>
                    <td>{{ course.todos }} offene {{ course.todos === 1 ? 'Aufgabe' : 'Aufgaben' }}</td>
                    <td class="cell-btn">
                        <button class="btn btn-secondary btn-160" @click="leave_course(course.id)">
                            <span class="d-none d-lg-inline">Kurs verlassen</span>
                            <fa-icon class="d-lg-none" :icon="['fas', 'trash']"></fa-icon>
                        </button>
                    </td>
                    <td class="cell-btn">
                        <a :href="`/course/${course.id}`" class="btn btn-primary btn-160">
                            <span class="d-none d-lg-inline">Öffnen</span>
                            <fa-icon class="d-lg-none" :icon="['fas', 'arrow-right']"></fa-icon>
                        </a>
                    </td>
                </tr>
            </tbody>
            <tbody v-else>
                <tr>
                    <td colspan="10" class="empty-msg">
                        <i>Keine Inhalte gefunden.</i>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
import { showSuccessAlert, showErrorAlert } from '../../../helpers/Alerts';

export default {
    data() {
        return {
            courses: [],
        };
    },
    mounted() {
        axios.get('courses')
            .then((response) => {
                this.courses = response.data;
                this.courses.forEach((course) => {
                    course.todos = course.counselling_setups.length - 1;
                    course.counselling_setups.forEach((setup) => {
                        if (setup.mandatory) {
                            axios.get('/counselling/setup/' + setup.id)
                            .then(res => {
                                if (res.data.length > 0) {
                                    course.todos--;
                                }
                            })
                            .catch(err => {
                                showErrorAlert(err);
                            })
                        }
                    })
                })
            })
            .catch((err) => {
                showErrorAlert(err);
            })
    },
    methods: {
        leave_course(course_id) {
            axios.delete(`course/${course_id}/leave`)
                .then((res) => {
                    this.courses = this.courses.filter((course) => course.id !== course_id);
                    showSuccessAlert(res.data.message);
                })
                .catch((err) => {
                    showErrorAlert(err);
                })
        },
    },
}
</script>
<style scoped lang="scss">
@import '../../../../css/general.scss';

    td {
        vertical-align: middle;
    }

    @include media-breakpoint-down(lg) {
        .btn-160 {
            width: auto !important;
        }
    }

</style>