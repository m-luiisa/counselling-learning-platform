<template>
    <div>
        <table class="table table-responsive" :class="{'smallView': smallView}">
            <thead>
                <tr class="d-none d-sm-table-row">
                    <th class="d-lg-none" v-if="!smallView"></th>
                    <th>
                        <span class="d-none d-sm-inline">Nachbereitung</span>
                    </th>
                    <th class="d-none d-sm-table-cell">Erstellt</th>
                    <th class="d-none d-md-table-cell" :class="{ 'hidden': smallView }">Beratungsstelle</th>
                    <th class="d-none d-md-table-cell" :class="{ 'hidden': smallView }">Persona</th>
                    <th>Titel</th>
                    <th class="d-none d-lg-table-cell">Feedback</th>
                    <th class="d-none d-lg-table-cell">PeerReview</th>
                    <th v-show="!smallView"></th>
                    <th style="width: 1%;"></th>
                </tr>
            </thead>
            <tbody v-for="(exercise, index) in exercises" :key="index">
                <exercise :data="exercise" :smallView="smallView" :courseFinished="courseFinished" @exerciseDeleted="exerciseDeleted"></exercise>
            </tbody>
            <tbody v-if="exercises.length === 0">
                <tr>
                    <td colspan="10" class="empty-msg">
                        <i>Keine Inhalte gefunden.</i>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="open-card open-exercises"><a class="link" :href="`/course/${courseId}/exercises`" v-if="smallView && exercises.length > 0">Zu den Übungen</a></div>
        <div v-if="!courseFinished" class="btn-new-exercise" :class="{'open-card position-relative': !smallView || exercises.length === 0,'mt-5': !smallView , 'mt-3': smallView && exercises.length === 0}">
            <a class="btn btn-primary btn-160" :href="`/counselling/create/${setupId}`">Neue Übung</a>
        </div>
    </div>
</template>
<script>
import { showErrorAlert} from '../../../helpers/Alerts';

export default {
    props: {
        smallView: {
            type: Boolean,
            default: false
        },
        setupId: {
            type: Number
        },
        courseEndDate: {
            type: String,
        },
    },
    data() {
        return {
            exercises: [],
            courseId: null,
        };
    },
    computed: {
        courseFinished() {
            const end = new Date(this.courseEndDate).setHours(0,0,0,0);
            const today = new Date().setHours(0,0,0,0);
            return today > end;
        },
    },
    mounted() {
        axios.get(`/counselling/setup/${this.setupId}`)
            .then(res => {
                if (res.data.length > 0) this.courseId = res.data[0].course;
                if (this.smallView) {
                    this.exercises = res.data.slice(0, 2);
                } else {
                    this.exercises = res.data;
                }
            })
            .catch(err => {
                showErrorAlert(err);
            })
    },
    methods: {
        exerciseDeleted(id) {
            this.exercises = this.exercises.filter(exercise => exercise.id !== id);
        },
    },
}
</script>
<style lang="scss" scoped>
@import '../../../../css/general.scss';

    .btn-new-exercise {
        position: absolute;
        bottom: 0.5rem;
        right: 1rem;
    }

    @include media-breakpoint-down(sm) {
        .open-exercises {
            text-align: left !important;
            margin-left: 1rem;
        }
    }

    tbody {
        background-color: transparent !important;
        &:last-of-type {
            border-bottom: 1px solid $border-color !important;
        }
    }
</style>