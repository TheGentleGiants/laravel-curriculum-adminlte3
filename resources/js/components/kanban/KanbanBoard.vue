<template>
    <div :style="kanbanWidth"
        class="m-0">
        <!-- Columns (Statuses) -->

        <draggable
            v-model="statuses"
            v-bind="columnDragOptions"
            @end="handleStatusMoved">
            <div
                v-for="status in statuses"
                :key="'header_'+status.id"
                class=" no-border pr-2"
                :style="'float:left;width:' + itemWidth + 'px;'">
                <div class="card-header border-bottom-0 p-0"
                     :key="status.id">
                    <strong>{{ status.title }}</strong>
                    <div class="btn btn-flat py-0 pl-0 pull-left"
                         data-toggle="dropdown"
                         aria-expanded="false">
                        <i class="text-muted fas fa-bars"></i>
                        <div class="dropdown-menu"
                             x-placement="top-start">
                            <span>
                                <button class="dropdown-item py-1" @click="">
                                    <i class="fa fa-pencil-alt mr-4"></i>
                                    {{ trans('global.kanbanStatus.edit') }}
                                </button>
                                <hr class="my-1">
                                <button
                                    v-can="'kanban_delete'"
                                    class="dropdown-item py-1 text-red"
                                    @click="deleteStatus(status)">
                                    <i class="fa fa-trash mr-4"></i>
                                    {{ trans('global.delete') }}
                                </button>
                            </span>
                        </div>
                    </div>
                     <div v-show="newItem !== status.id"
                         class="btn btn-flat py-0 mr-2 pull-right"
                         @click="openForm('item', status.id)">
                         <i class="text-muted fa fa-plus-circle"></i>
                     </div>
                </div>

                <KanbanItemCreate
                    v-if="newItem === status.id"
                    :status="status"
                    :item="item"
                    :width="itemWidth"
                    v-on:item-added="handleItemAdded"
                    v-on:item-updated="handleItemUpdated"
                    v-on:item-canceled="closeForm"
                    style=" z-index: 2"></KanbanItemCreate>
                <div style="position:absolute; top:35px; bottom:0;overflow-y:scroll; z-index: 1"
                     :style="'width:' + itemWidth + 'px;'">
                    <draggable
                        class="flex-1 overflow-hidden"
                        v-model="status.items"
                        v-bind="itemDragOptions"
                        style="min-height:500px;"
                        @end="handleItemMoved"
                        filter=".ignore">
                        <transition-group
                            v-for="item in status.items"
                            :key="'transition_group-'+item.id"
                            style="min-height:50px; display:flex;flex-direction: column;"
                            :style="'width:' + itemWidth + 'px;'"
                            class="pr-3"
                            tag="span">
                            <!-- Items -->
                            <span :key="item.id">
                                 <KanbanItem

                                     :ref="'kanbanItemId' + item.id"
                                     :item="item"
                                     :width="itemWidth"
                                     v-on:item-destroyed="handleItemDestroyed"
                                     v-on:item-edit="handleItemEdit"/>
                            </span>
                            <!--  ./Items -->
                        </transition-group>
                    </draggable>
                </div>
            </div>
            <div class=" no-border  pr-2"
                 style="float:left;"
                 :style="'width:' + itemWidth + 'px;'">
                    <div class="card-header border-bottom-0 p-0">
                        <strong class="text-secondary btn px-1 py-0"  @click="openForm('status')">
                            <i class="fa fa-plus"></i> {{ trans('global.kanbanStatus.create') }}
                        </strong>
                    </div>
                <KanbanStatusCreate
                v-if="newStatus === 1"
                :kanban_id="kanban.id"
                :order_id="newStatusId"
                v-on:status-added="handleStatusAdded"
                v-on:status-canceled="closeForm"/>
            </div>
        </draggable>
        <!-- ./Columns -->
    </div>
</template>

<script>
    import draggable from "vuedraggable"; // import the vuedraggable
    import KanbanItem from './KanbanItem';
    import KanbanItemCreate from "./KanbanItemCreate";
    import KanbanStatusCreate from "./KanbanStatusCreate";


    export default {

        props: {
            'kanban': Object,
            'search': ''
        },
        data() {
            return {
                statuses: this.kanban.statuses,
                newItem: 0, // track the ID of the status we want to add to
                newStatus: 0,
                itemWidth: 320,
                item: null,
            };
        },
        methods: {
                handleStatusMoved() {
                    // Send the entire list of statuses to the server
                    axios.put("/kanbanStatuses/sync", {columns: this.statuses})
                            .then(res => { // Tell the parent component we've added a new task and include it
                                this.statuses = res.data.message;
                            })
                            .catch(err => {
                                console.log(err.response);
                             });
                 },
                openForm(type, value = 1) {
                    this.item = null;
                    if (type == 'status'){
                        this.newStatus = value;

                    } else {
                        this.newItem = value;
                    }

                },
                // reset the statusId and close form
                closeForm() {
                  this.newStatus = 0;
                  this.newItem = 0;
                },
                handleStatusAdded(newStatus){
                    newStatus['items'] = [];            //add items to prevent error if item is created without reloading page
                    this.statuses.push(newStatus);
                    this.closeForm();
                },
                handleItemAdded(newItem) {      // add a item to the correct column in our list
                    const statusIndex = this.statuses.findIndex(            // Find the index of the status where we should replace the item
                        status => status.id === newItem.kanban_status_id
                    );
                    this.statuses[statusIndex].items.push(newItem);       // Add newly created item to our column

                    this.closeForm();                                     // Reset and close the AddItemForm
                },

                handleItemMoved() {
                    // Send the entire list of statuses to the server
                    axios.put("/kanbanItems/sync", {columns: this.statuses})
                            .then(res => { // Tell the parent component we've added a new task and include it
                                this.statuses = res.data.message;
                            })
                            .catch(err => {
                                console.log(err.response);
                             });
                 },
                handleItemDestroyed(item){
                    const statusIndex = this.statuses.findIndex(            // Find the index of the status where we should add the item
                        status => status.id === item.kanban_status_id
                    );

                    let index = this.statuses[statusIndex].items.indexOf(item);

                    this.statuses[statusIndex].items.splice(index, 1);
                },
                handleItemEdit(item){
                    this.newItem = item.kanban_status_id;
                    this.item = item;
                },
                handleItemUpdated(updatedItem){
                    const statusIndex = this.statuses.findIndex(            // Find the index of the status where we should replace the item
                        status => status.id === updatedItem.kanban_status_id
                    );
                    const itemIndex = this.statuses[statusIndex].items.findIndex(   // Find the index of the item where we should replace the item
                        item => item.id === updatedItem.id
                    );

                    this.statuses[statusIndex].items[itemIndex] = updatedItem;       // Add updated item to our column

                    this.closeForm();                                     // Reset and close the AddItemForm
                    this.item =  null; //re
                },
                deleteStatus(status){
                    if (confirm( window.trans.global.kanbanStatus.delete_helper )) {
                        axios.delete("/kanbanStatuses/"+status.id)
                                .then(res => { // Tell the parent component we've added a new task and include it
                                    this.handleStatusDestroyed(status);
                                })
                                .catch(err => {
                                    console.log(err.response);
                                 });
                    }
                },
                handleStatusDestroyed(status){
                    let index = this.statuses.indexOf(status);
                    this.statuses.splice(index, 1);
                },


        },
        computed: {
            columnDragOptions() {
                return {
                  animation: 200,
                  group: "column-list",
                  dragClass: "status-drag"
                };
            },
            itemDragOptions() {
                return {
                  animation: 200,
                  group: "item-list",
                  dragClass: "status-drag"
                };
            },
            kanbanWidth() {
                return "width: "+ ((this.statuses.length) * this.itemWidth +this.itemWidth) +"px;";
            },
            newStatusId() {
                return this.statuses.length;
            }

        },

        mounted() {

        },
        components: {
            draggable,
            KanbanItem,
            KanbanItemCreate,
            KanbanStatusCreate,
        }

    }
</script>
<style scoped>
.status-drag {
    transition: transform 0.5s;
    transition-property: all;
}
</style>
