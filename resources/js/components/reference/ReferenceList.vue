<template>
    <div>
        <span v-for="terminal_subscription in entry.terminal_objective_subscriptions">
            <objective-box
                v-bind:key="terminal_subscription.id"
                :objective="terminal_subscription.terminal_objective"
                :settings="settings"
                type="terminal">
            </objective-box>
            <button
                type="button"
                class="btn btn-tool"
                @click.prevent="del('terminal', terminal_subscription)">
                <i class="fa fa-trash "></i>
            </button>
        </span>
        <span class="clearfix"></span>
        <span v-for="enabling_subscription in entry.enabling_objective_subscriptions"
        >
            <objective-box
                :objective="enabling_subscription.enabling_objective.terminal_objective"
                :settings="settings"
                type="terminal">
            </objective-box>
            <objective-box
                :objective="enabling_subscription.enabling_objective"
                :settings="settings"
                type="enabling">
            </objective-box>
            <button
                type="button"
                class="btn btn-tool"
                @click.prevent="del('enabling', enabling_subscription)">
                <i class="fa fa-trash "></i>
            </button>
            <span class="clearfix"></span>
        </span>
        <table
            v-permission="'reference_create'"
            class="table table-hover datatable media_table">
            <tr>
                <td
                    class="py-2 link-muted text-sm pointer"
                    v-permission="'lms_create'"
                    @click.prevent="open('subscribe-objective-modal')">
                    <i class="fa fa-plus px-2 "></i> {{
                        trans('global.terminalObjective.title')
                    }}/{{ trans('global.enablingObjective.title') }}
                </td>
            </tr>
        </table>
    </div>
</template>
<script>
import ObjectiveBox from '../objectives/ObjectiveBox'

export default {
    name: 'reference-list',
    components: {ObjectiveBox},
    props: {
        entry: {},
        'subscribable_type': String,
        'subscribable_id': Number,
    },
    data() {
        return {
            settings: {
                'edit': false,
            },
        }
    },
    methods: {
        open(modal) {
            this.$modal.show(modal, {
                'referenceable_type': this.subscribable_type,
                'referenceable_id': this.subscribable_id
            });
        },

        del(type, subscription) { //id of external reference and value in db
            axios.post('/' + type + 'ObjectiveSubscriptions/destroy', subscription)
                .then((res) => {

                })
                .catch((error) => {
                    console.log(error);
                });

        },

    }
}
</script>
