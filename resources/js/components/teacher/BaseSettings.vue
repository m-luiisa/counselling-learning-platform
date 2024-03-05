<template>
    <div>
        <div class="row mb-3">
            <div class="col-sm-12">
                <label>Kursname</label>
                <input id="name" type="text" class="form-control" v-model="inputs.name" :class="{ 'is-invalid': v$.inputs.name.$error }" :disabled="disabled">
                <div v-if="v$.inputs.name.$error" class="invalid-feedback">
                    {{ v$.inputs.name.$errors[0].$message }}
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-12">
                <label>Einschreibeschlüssel</label>
                <input id="enrollment-key" type="text" class="form-control" v-model="inputs.enrollment_key" :class="{ 'is-invalid': v$.inputs.enrollment_key.$error }" :disabled="disabled">
                <div v-if="v$.inputs.enrollment_key.$error" class="invalid-feedback">
                    {{ v$.inputs.enrollment_key.$errors[0].$message }}
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-6">
                <label>Startdatum</label>
                <input id="start-date" type="date" class="form-control" v-model="inputs.start_date" :class="{ 'is-invalid': v$.inputs.start_date.$error }" :disabled="disabled"/>
                <div v-if="v$.inputs.start_date.$error" class="invalid-feedback">
                    {{ v$.inputs.start_date.$errors[0].$message }}
                </div>
            </div>
            <div class="col-sm-6">
                <label>Enddatum</label>
                <input id="end-date" type="date" class="form-control" v-model="inputs.end_date" :class="{ 'is-invalid': v$.inputs.end_date.$error }" :disabled="disabled"/>
                <div v-if="v$.inputs.end_date.$error" class="invalid-feedback">
                    {{ v$.inputs.end_date.$errors[0].$message }}
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { useVuelidate } from '@vuelidate/core';
import { required, helpers } from '@vuelidate/validators';
import { showSuccessAlert , showErrorAlert} from '../../helpers/Alerts';


export default {
    props: [
        'id', 'disabled'
    ],
    data() {
        return {
            inputs: {
                name: '',
                enrollment_key: '',
                start_date: null,
                end_date: null,
            },
            v$: useVuelidate(),
        };
    },
    validations() {
        return {
            inputs: {
                name: { required: helpers.withMessage('Pflichtfeld', required), },
                enrollment_key: { required: helpers.withMessage('Pflichtfeld', required) },
                start_date: { 
                    required: helpers.withMessage('Pflichtfeld', required),
                },
                end_date: { 
                    required: helpers.withMessage('Pflichtfeld', required),
                    minValue: 
                        helpers.withMessage('Das Enddatum muss nach dem Startdatum liegen', 
                        (val, { start_date }) => {
                            return new Date(val) > new Date(start_date);
                        }),
                },
            },
        };
    },
}
</script>
<style lang="">
    
</style>