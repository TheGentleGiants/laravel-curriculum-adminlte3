<template >

    <span v-if="type === 'enabling' && settings.edit === false">

        <i class="t-18 margin-r-5 text-green pointer"
           v-bind:class="[green_css, fabadge]"
           v-bind:data-count="[green_count]"
           @click.prevent="achieve('1')">
        </i>
        <i class="t-18 margin-r-5 text-orange pointer"
           v-bind:class="[orange_css, fabadge]"
           v-bind:data-count="[orange_count]"
           @click.prevent="achieve('2')">
        </i>
        <i class="t-18 margin-r-5 text-red pointer"
           v-bind:class="[red_css, fabadge]"
           v-bind:data-count="[red_count]"
           @click.prevent="achieve('3')">
        </i>
        <i class="t-18 margin-r-5 text-gray pointer"
           v-bind:class="[white_css, fabadge]"
           v-bind:data-count="[white_count]"
           @click.prevent="achieve('0')">
        </i>
        
    </span>

</template>


<script>
    export default {
        props: {
                objective: {},
                type:{},
                settings:{},
                users:{
                    type: Array,
                    default: () => []
                }
            },
         data () {
            return {
                status: '00',
                green: 'far fa-circle',
                orange: 'far fa-circle',
                red: 'far fa-circle',
                white: 'far fa-circle',
                green_student_count: 0,
                green_teacher_count: 0,
                orange_student_count: 0,
                orange_teacher_count: 0,
                red_student_count: 0,
                red_teacher_count: 0,
                white_student_count: 0,
                white_teacher_count: 0,
            };
        },
        methods: {

            async achieve(status){
                var selected = this.users;
                if (selected.length === 0){
                    selected = ($('#users-datatable').DataTable().rows({ selected: true }).ids().toArray())
                }
                let archievement = {
                    'referenceable_type': (this.type === 'terminal' ? 'App\\TerminalObjective' : 'App\\EnablingObjective'),
                    'referenceable_id': this.objective.id,
                    'user_id': selected,
                    'status': status
                }
                try {
                    this.status = (await axios.post('/achievements', archievement)).data.message;
//                    calculateProgress(); //todo?
                } catch(error) {
                    alert(error);
                }
            },
            calculate_css(number) {
                var status = "far fa-circle";

                if (this.status.charAt(0) === number &&
                    this.status.charAt(1) === number){
                    status = "fa fa-check-circle";
                } else if (this.status.charAt(0) === number){
                    status = "fa fa-circle";
                } else if (this.status.charAt(1) === number){
                    status = "far fa-check-circle";
                }

                return status;
            },
            calculate_count(number) {
                var student = 0, teacher = 0;
                if (typeof this.objective.achievements !== 'undefined'){
                    if (typeof this.objective.achievements[0] === 'object' && this.settings.achievements === true) {
                        for (let i = 0; i < (this.objective.achievements).length; i++){
                            //console.log('ena:'+ this.objective.id +' lenght:'+ this.objective.achievements.length+' i:'+i+' student: '+this.objective.achievements[i].status.charAt(0)+' teacher: '+ this.objective.achievements[i].status.charAt(1));
                            if (this.objective.achievements[i].status.charAt(0) === number &&
                                this.objective.achievements[i].status.charAt(1) === number){
                                 student++;
                                 teacher++;
                             }
                             else if (this.objective.achievements[i].status.charAt(0) === number){
                                 student++;
                             }
                             else if (this.objective.achievements[i].status.charAt(1) === number){
                                 teacher++;
                             }
                        }
                         return teacher; //todo option to show students self achievement status
                    } else {
                        if (typeof this.objective.achievements !== 'undefined'){
                            if (typeof this.objective.achievements[0] === 'object') {
                                this.status = this.objective.achievements[0].status;
                            }
                        } else {
                            this.status = '00';
                        }

                    }
                } else {
                     return;
                }
            }
        },
        computed: {
            green_css: function () {
                return this.calculate_css('1');
            },
            orange_css: function () {
                return this.calculate_css('2');
            },
            red_css: function () {
                return this.calculate_css('3');
            },
            white_css: function () {
                return this.calculate_css('0');
            },
            //counts
            green_count: function () {
                return this.calculate_count('1');
            },
            orange_count: function () {
                return this.calculate_count('2');
            },
            red_count: function () {
                return this.calculate_count('3');
            },
            white_count: function () {
                return this.calculate_count('0');
            },
            fabadge: function (){
                if (window.Laravel.permissions.indexOf('achievement_access') !== -1){
                    return "fabadge";
                }

            }
        },
        watch: {
            objective: function (val, oldVal) {
                if (typeof this.objective.achievements[0] === 'object'){
                    //console.log(val.achievements[0].status);
                    this.status = val.achievements[0].status;
                } else {
                    this.status = '00';
                }
            },
        },
        created() {

            if (typeof this.objective.achievements !== 'undefined'){
                if (typeof this.objective.achievements[0] === 'object') {
                    this.status = this.objective.achievements[0].status;
                }
            }
        },

    }
</script>
