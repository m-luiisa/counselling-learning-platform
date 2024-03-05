<template>
    <div>
        <table class="table table-responsive mb-0">
            <thead>
                <tr class="d-none d-sm-table-row">
                    <th class="d-lg-none" style="width: 2%;"></th>
                    <th>Name</th>
                    <th class="d-none d-sm-table-cell">E-Mail</th>
                    <th class="d-none d-lg-table-cell">Pseudonym</th>
                    <th class="d-none d-md-table-cell">Rechte</th>
                    <th></th>
                </tr>
            </thead>
            <tbody v-for="user in users">
                <tr class="main-row">
                    <td class="d-lg-none pt-3 pb-3" style="width: 1%;">
                        <a class="btn-toggle" data-bs-toggle="collapse" :href="'#userCollapse' + user.id"
                            role="button" aria-expanded="false"
                            :aria-controls="'userCollapse' + user.id">
                            <fa-icon :icon="['fas', 'angle-right']" class="icon-closed"></fa-icon>
                            <fa-icon :icon="['fas', 'angle-down']" class="icon-opened d-none"></fa-icon>
                        </a>
                    </td>
                    <td>
                        {{ user.name }}
                    </td>
                    <td class="d-none d-sm-table-cell">{{ user.email }}</td>
                    <td class="d-none d-lg-table-cell">{{ pseudonym(user.id) }}</td>
                    <td class="d-none d-md-table-cell">
                        <select v-model="user.role_id" @change="changeRole(user)" class="form-select" :disabled="(user.role_id === teacher_role_id && single_teacher) || disabled">
                            <option v-for="option in possible_roles" :value="option.id">
                                {{ option.text }}
                            </option>
                        </select>
                    </td>
                    <td>
                        <button class="btn btn-danger btn-sm btn--full-width" @click="deleteUser(user.id)" :disabled="user.role_id === teacher_role_id && single_teacher">
                            <span class="d-none d-lg-inline">Entfernen</span>
                            <fa-icon class="d-lg-none" :icon="['fas', 'trash']"></fa-icon>
                        </button>
                    </td>
                </tr>
                <!-- collapsable details-->
                <tr class="collapse d-lg-none" :id="'userCollapse' + user.id">
                    <td></td>
                    <td colspan="10" class="pb-4">
                        <span>
                            <div class="d-sm-none mb-2"><b>E-Mail: </b>{{ user.email }}</div>
                            <div><b>Pseudonym:</b> {{ pseudonym(user.id) }}</div>
                            <div class="d-md-none mt-2">
                                <b>Rechte: </b>
                                <select v-model="user.role_id" @change="changeRole(user)" class="form-select" :disabled="(user.role_id === teacher_role_id && single_teacher) || disabled">
                                    <option v-for="option in possible_roles" :value="option.id">
                                        {{ option.text }}
                                    </option>
                                </select>
                            </div>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
import { showSuccessAlert, showErrorAlert } from '../../helpers/Alerts';
import { getFullPseudonym } from '../../helpers/Pseudonym';

export default {
    props: {
        id: {
            type: Number,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            users: [],
            possible_roles: [],
        };
    },
    computed: {
        pseudonym: function () {
            return (userId) => {
                const user = this.users.find((user) => user.id === userId);
                return getFullPseudonym(user.pseudo_first_name, user.pseudo_last_name);
            };
        },
        single_teacher() {
            const teachers = this.users.filter((user) => user.role_id === this.teacher_role_id);
            return teachers.length === 1;
        },
        teacher_role_id() {
            return this.possible_roles.find((role) => role.title === 'editingteacher')?.id;
        }
    },
    mounted() {
        this.loadUsers();
        this.loadRoles();
    },
    methods: {
        loadUsers() {
            axios.get(`/course/${this.id}/members`)
                .then((res) => {
                    this.users = res.data;
                })
                .catch((err) => {
                    showErrorAlert(err);
                })
        },

        loadRoles() {
            axios.get('/course/roles')
                .then((res) => {
                    this.possible_roles = res.data;
                })
                .catch((err) => {
                    showErrorAlert(err);
                })
        },

        changeRole(user) {
            axios.put('/course/members/' + user.id, {
                role_id: user.role_id
            })
            .then(res => {
                showSuccessAlert(res.data.message);

            })
            .catch(err => {
                showErrorAlert(err);
                if (err?.response?.data?.current_role) {
                    const index = this.users.findIndex(u => u.id === user.id);
                    if (index !== -1) {
                        this.users[index].role_id = err.response.data.current_role;
                    }
                }
            })
        },

        deleteUser(userId) {
            axios.delete(`/course/members/${userId}`)
            .then(res => {
                const index = this.users.findIndex(user => user.id === userId);
                if (index !== -1) {
                    this.users.splice(index, 1);
                }
                showSuccessAlert(res.data.message);

            })
            .catch(err => {
                showErrorAlert(err);
            })
        },
    }
}
</script>
<style scoped lang="scss">
@import '../../../css/general.scss';

    td {
        vertical-align: middle;
    }
</style>