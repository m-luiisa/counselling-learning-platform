<template>
    <tr>
        <td class="d-lg-none pt-3 pb-3" style="width: 1%;" v-if="!smallView">
            <a class="btn-toggle" data-bs-toggle="collapse" :href="'#taskCollapse' + data.id"
                role="button" aria-expanded="false"
                :aria-controls="'taskCollapse' + data.id">
                <fa-icon :icon="['fas', 'angle-right']" class="icon-closed"></fa-icon>
                <fa-icon :icon="['fas', 'angle-down']" class="icon-opened d-none"></fa-icon>
            </a>
        </td>
        <td>
            <div class="d-flex align-items-center">
                <div class="circle d-none d-sm-block" :class="{'done': done}"></div>
                <span class="d-none d-sm-block">
                    {{done ? 'beendet' : 'offen'}}
                </span>
                <span class="d-sm-none" :class="{'text-danger': !done, 'text-success': done}">
                    <fa-icon v-if="done" :icon="['fas', 'circle-check']" style="width: 16px; height: 16px"></fa-icon>
                    <fa-icon v-else :icon="['fas', 'circle-xmark']" style="width: 16px; height: 16px;"></fa-icon>
                </span>
            </div>
        </td>
        <td :class="{'text-danger': (deadline_today || deadline_over) && !done}">{{ due_date }}</td>
        <td class="d-none d-md-table-cell" :class="{ 'hidden': smallView }">{{ counselling_field }}</td>
        <td>{{ persona }}</td>
        <td class="d-none d-sm-table-cell">{{ data.counselling?.length > 0 ? data.counselling[0].title : '' }}</td>
        <td class="d-none d-lg-table-cell"></td> <!-- Feedback -->
        <td class="d-none d-lg-table-cell"></td> <!-- Peer Review -->
        <td v-show="!smallView" class="cell-btn">
            <button v-if="!courseFinished" class="btn btn-secondary" @click="deleteCounselling" :disabled="(!done && !in_progress) || deadline_over">
                <span class="d-none d-xl-inline">Beratung zurücksetzen</span>
                <fa-icon class="d-xl-none" :icon="['fas', 'rotate-left']"></fa-icon>
            </button>
        </td>
        <td class="cell-btn">
            <button class="btn btn-primary btn-160" :disabled="!done && deadline_over || (!done && courseFinished)" @click="openTask">
                <span class="d-none d-xl-inline">{{done || in_progress? 'Öffnen' : 'Starten'}}</span>
                <fa-icon class="d-xl-none" :icon="['fas', 'arrow-right']"></fa-icon>
            </button>
        </td>
    </tr>
    <!-- collapsable details-->
    <tr class="collapse d-lg-none" :id="'taskCollapse' + data.id" v-if="!smallView">
        <td></td>
        <td colspan="10" class="pb-4">
            <span>
                <div class="d-md-none mb-2"><b>Beratungsstelle: </b>{{ counselling_field }}</div>
                <div class="d-sm-none mb-2" v-if="data.counselling?.length > 0"><b>Titel: </b>{{  data.counselling[0].title }}</div>
                <div class="d-lg-none mb-2"><b>Feedback: </b></div>
                <div class="d-lg-none mb-2"><b>PeerReview: </b></div>

            </span>
        </td>
    </tr>

</template>
<script>
import { showSuccessAlert , showErrorAlert} from '../../../helpers/Alerts';

export default {
emits: ['taskReseted'],
props: {
    data: {
        type: Object,
    },
    smallView: {
        type: Boolean,
        default: false
    },
    courseFinished: {
        type: Boolean,
    }
},
computed: {
    due_date() {
        const date = new Date(this.data.due_date);
        return date.toLocaleDateString('de');
    },
    done() {
        if (this.data.counselling !== undefined) {
            return this.data.counselling.length > 0 && this.data.counselling[0].status_chat == 'done';
        } else {
            return false;
        }
    },
    in_progress() {
        if (this.data.counselling !== undefined) {
            return this.data.counselling.length > 0 && this.data.counselling[0].status_chat == 'in progress';
        } else {
            return false;
        }
    },
    persona() {
        if (this.data.personae) {
            return this.data.personae?.length > 1 ? 'Zufällig' : this.data.personae[0].name;
        } else {
            return '';
        }
    },
    counselling_field() {
        const fields = this.data.personae?.map((persona) => persona.counselling_field);
        if (fields) {
            return fields.length > 1 ? 'Zufällig' : fields[0];
        } else {
            return '';
        }
    },
    deadline_over() {
        const today = new Date().setHours(0,0,0,0);
        return today > new Date(this.data.due_date).setHours(0,0,0,0);
    },
    deadline_today() {
        const today = new Date().setHours(0,0,0,0);
        return today === new Date(this.data.due_date).setHours(0,0,0,0);
    },
},
methods: {
    openTask() {
        if (this.done || this.in_progress) {
            window.location.href = `/counselling/${this.data.counselling[0].id}`
        } else {
            axios.post('/counselling/' + this.data.id)
            .then((res) => {
                window.location.replace('/counselling/' + res.data.id);
            })
            .catch((err) => {
                showErrorAlert(err);
            })
        }
    },

    deleteCounselling() {
        axios.delete('/counselling/' + this.data.counselling[0].id)
        .then(res => {
            this.$emit('taskReseted', this.data.id);
            showSuccessAlert(res.data.message);
        })
        .catch(err => {
            showErrorAlert(err);
        })
    },
},
}
</script>
<style lang="scss" scoped>
@import '../../../../css/general.scss';

.circle {
    width: 15px;
    height: 15px;
    background-color: $red;
    display: inline-block;
    margin-right: 8px;
    &.done {
        background-color: $green;
    }
}

@include media-breakpoint-down(xl) {
    .btn-160 {
        width: auto;
    }
}
</style>