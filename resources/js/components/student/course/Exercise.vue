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
                <div class="circle d-none d-sm-block" :class="{'done': status === 0}"></div>
                <span class="d-none d-sm-block">
                    {{status > 0 ? status + '/3 offen' : 'beendet'}}
                </span>
                <span class="d-sm-none text-danger d-flex align-items-center gap-2" v-if="status > 0">
                    <fa-icon :icon="['fas', 'circle-xmark']" style="width: 16px; height: 16px;"></fa-icon>
                    <span>{{ status + '/3' }}</span>
                </span>
                <span class="d-sm-none text-success" v-if="status === 0">
                    <fa-icon :icon="['fas', 'circle-check']" style="width: 16px; height: 16px"></fa-icon>
                </span>
            </div>
        </td>
        <td class="d-none d-sm-table-cell">{{ started_at }}</td>
        <td class="d-none d-md-table-cell" :class="{ 'hidden': smallView }">{{ data.counselling_field }}</td>
        <td class="d-none d-md-table-cell" :class="{ 'hidden': smallView }">{{ data.persona }}</td>
        <td>{{ data.title }}</td>
        <td class="d-none d-lg-table-cell"></td> <!-- Feedback -->
        <td class="d-none d-lg-table-cell"></td> <!-- Peer Review -->
        <td v-show="!smallView" class="cell-btn">
            <button v-if="!courseFinished" class="btn btn-secondary" @click="deleteCounselling">
                <span class="d-none d-xl-inline">Beratung löschen</span>
                <fa-icon class="d-xl-none" :icon="['fas', 'trash']"></fa-icon>

            </button>
        </td>
        <td class="cell-btn">
            <a class="btn btn-primary btn-160" :href="`/counselling/${data.id}`">
                <span class="d-none d-xl-inline">Öffnen</span>
                <fa-icon class="d-xl-none" :icon="['fas', 'arrow-right']"></fa-icon>
            </a>
        </td>
    </tr>
    <!-- collapsable details-->
    <tr class="collapse d-lg-none" :id="'taskCollapse' + data.id" v-if="!smallView">
    <td></td>
    <td colspan="10" class="pb-4">
        <span>
            <div class="d-sm-none mb-2"><b>Erstellt: </b>{{ started_at }}</div>
            <div class="d-md-none mb-2"><b>Beratungsstelle: </b>{{ data.counselling_field }}</div>
            <div class="d-md-none mb-2"><b>Persona: </b>{{ data.persona }}</div>
            <div class="d-lg-none mb-2"><b>Feedback: </b></div>
            <div class="d-lg-none mb-2"><b>PeerReview: </b></div>

        </span>
    </td>
</tr>
</template>
<script>
import { showSuccessAlert , showErrorAlert} from '../../../helpers/Alerts';

export default {
    emits: ['exerciseDeleted'],
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
        started_at() {
            const date = new Date(this.data.start_date);
            return date.toLocaleDateString('de');
        },
        status() {
            const options = [this.data.note, this.data.feedback, this.data.peerReview];
            const i = options.filter(option => option != null && (option.status === 'done' || option.done)).length;
            return 3 - i;
        }
    },
    methods: {
        deleteCounselling() {
            axios.delete('/counselling/' + this.data.id)
            .then(res => {
                this.$emit('exerciseDeleted', this.data.id);
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