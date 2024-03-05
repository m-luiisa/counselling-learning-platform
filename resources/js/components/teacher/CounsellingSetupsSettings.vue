<template>
    <div>
        <div class="form-group row align-items-center">
            <div class="col-md-3 col-sm-12">
                <label>Fälligkeitsdatum:</label>
            </div>
            <div class="col-auto">
                <input type="date" v-model="setup.due_date" class="form-control" :class="{ 'is-invalid': v$.setup.due_date.$error }" :disabled="disabled"/>
                <div v-if="v$.setup.due_date.$error" class="invalid-feedback">
                    {{ v$.setup.due_date.$errors[0].$message }}
                </div>
            </div>
        </div>
        <div class="form-group row align-items-center">
            <div class="col-md-3 col-sm-12">
                <label>Beratungsstelle(n):</label>
            </div>
            <div class="col-sm-9">
                <multiselect v-model="setup.settings.counselling_fields"
                    :options="all_fields"
                    label="name"
                    selectLabel=""
                    deselectLabel=""
                    placeholder="Beratungsstellen aus-/abwählen"
                    :multiple="true"
                    track-by="id"
                    :class="{ 'is-invalid': v$.setup.settings.counselling_fields.$error }"
                    :disabled="disabled">
                </multiselect>
                <div v-if="v$.setup.settings.counselling_fields.$error" class="invalid-feedback">
                    {{ v$.setup.settings.counselling_fields.$errors[0].$message }}
                </div>
            </div>
        </div>
        <div class="form-group row align-items-center">
            <div class="col-md-3 col-sm-12">
                <label>Persona(e):</label>
            </div>
            <div class="col-sm-9">
                <multiselect v-model="setup.settings.personae"
                    :options="availablePersonae"
                    label="name"
                    selectLabel=""
                    deselectLabel=""
                    placeholder="Personae aus-/abwählen"
                    :multiple="true"
                    track-by="id"
                    :class="{ 'is-invalid': v$.setup.settings.personae.$error }"
                    :disabled="disabled">
                </multiselect>
                <div v-if="v$.setup.settings.personae.$error" class="invalid-feedback">
                    {{ v$.setup.settings.personae.$errors[0].$message }}
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import multiselect from '@suadelabs/vue3-multiselect';
import { useVuelidate } from '@vuelidate/core';
import { helpers, required } from '@vuelidate/validators';

export default {
    components: { multiselect },
    props: {
        setup: {
            type: Object,
        },
        all_personae: {
            type: Array,
        },
        all_fields: {
            type: Array,
        },

        course_duration: {
            type: Object,
        },

        disabled: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            v$: useVuelidate(),
        }
    },

    validations() {
        return {
            setup: {
                due_date: { 
                    required: helpers.withMessage('Pflichtfeld', required),
                    inBetweenCourse:
                        helpers.withMessage('Das Fälligkeitsdatum muss innerhalb des Kurszeitraumes liegen', 
                        (val) => {
                            return (new Date(val) <= new Date(this.course_duration.end)) && (new Date(val) > new Date(this.course_duration.start));
                        }),
                },
                settings: {
                    counselling_fields: { required: helpers.withMessage('Pflichtfeld', required), },
                    personae: { required: helpers.withMessage('Pflichtfeld', required), },
                }
            }
        };
    },

    computed: {
        availablePersonae() {
            return this.all_personae.filter((persona) => this.setup.settings.counselling_fields.some((field) => field.id === persona.field));
        },
    },

    watch: {
        availablePersonae: {
            // check if selected personae are still available after editing fields
            handler(newAvailablePersonae) {
                this.setup.settings.personae = this.setup.settings.personae.filter(persona =>
                    newAvailablePersonae.some(availablePersona => availablePersona.id === persona.id)
                );
            },
            deep: true
        },
    }
}
</script>
<style lang="">
    
</style>