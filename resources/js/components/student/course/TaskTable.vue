<template>
    <div>
        <table class="table table-responsive" :class="{'smallView': smallView}">
            <thead>
                <tr>
                    <th class="d-lg-none" v-if="!smallView"></th>
                    <th style="width: 10%;">
                        <span class="d-none d-sm-inline">Status</span>
                    </th>
                    <th>Fällig am</th>
                    <th class="d-none d-md-table-cell" :class="{ 'hidden': smallView }">Beratungsstelle</th>
                    <th>Persona</th>
                    <th class="d-none d-sm-table-cell">Titel</th>
                    <th class="d-none d-lg-table-cell">Feedback</th>
                    <th class="d-none d-lg-table-cell">PeerReview</th>
                    <th v-show="!smallView"></th>
                    <th style="width: 1%;"></th>
                </tr>
            </thead>
            <tbody v-for="(task, index) in tasks" :key="index">
                <task :data="task" :smallView="smallView" :courseFinished="courseFinished" @taskReseted="taskReseted"></task>
            </tbody>
            <tbody v-if="tasks.length === 0">
                <tr>
                    <td colspan="10" class="empty-msg">
                        <i>Keine Inhalte gefunden.</i>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="open-card"><a class="link" :href="`/course/${courseId}/tasks`" v-if="smallView">Zu den Aufgaben</a></div>
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
        setups: {
            type: Object
        },
        courseEndDate: {
            type: String,
        },
    },
    data() {
        return {
            tasks: [],
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
        if (this.smallView) {
            this.tasks = Object.values(this.setups).slice(0, 2);
        } else {
            this.tasks = Object.values(this.setups);
        }
        this.courseId = this.tasks[0].course_id;
        this.loadCounsellings();
    },

    methods: {
        taskReseted() {
            this.loadCounsellings();
        },

        loadCounsellings() {
            this.tasks.forEach((task) => {
            axios.get(`/counselling/setup/${task.id}`)
            .then(res => {
                task.counselling = res.data;
            })
            .catch(err => {
                showErrorAlert(err);
            })
        })
        }
    },
}
</script>
<style lang="scss" scoped>
@import '../../../../css/general.scss';

tbody {
    background-color: transparent !important;
    &:last-of-type {
        border-bottom: 1px solid $border-color !important;
    }
}
</style>