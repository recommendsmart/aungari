class DateTimeValueControl extends Rete.Control {

    constructor(emitter, key, readonly) {
        super(key);
        this.component = {
            components: {
                // Component included for Multiselect.
                vuejsDatepicker
            },
            props: ['ikey', 'getData', 'putData', 'emitter'],
            template: `<div class="form-control">
            <label>Select date and time</label>
            <vuejs-datepicker v-model="value" @selected="fieldDateChanged"></vuejs-datepicker>
            <vue-timepicker format="hh:mm A" :format="Format" v-model="Data" @change="updateTime"></vue-timepicker>
          </div>`,
            data() {
                return {
                    type: drupalSettings.if_then_else.nodes.date_time_value.type,
                    class: drupalSettings.if_then_else.nodes.date_time_value.class,
                    name: drupalSettings.if_then_else.nodes.date_time_value.name,
                    classArg: drupalSettings.if_then_else.nodes.date_time_value.classArg,
                    Format: 'hh:mm:ss a',
                    Data: {
                      hh: '',
                      mm: '',
                      ss: '',
                      a: ''
                    },
                    value: ''
                }
            },
            methods: {
                fieldDateChanged(date){
                  this.value = date.toLocaleString();
                  this.putData('value',this.value);
                  editor.trigger('process');
                },
                updateTime(eventData){
                  var date = new Date(this.value);
                  date.setHours(parseInt(eventData.data.HH),parseInt(eventData.data.mm),parseInt(eventData.data.ss));
                  if(date instanceof Date && !isNaN(date)){
                    this.value = date.toLocaleString();
                  }
                  this.putData('value',this.value);
                  editor.trigger('process');
                }
            },
            mounted() {
              this.putData('type',drupalSettings.if_then_else.nodes.date_time_value.type);
              this.putData('class',drupalSettings.if_then_else.nodes.date_time_value.class);
              this.putData('name', drupalSettings.if_then_else.nodes.date_time_value.name);
              this.putData('classArg', drupalSettings.if_then_else.nodes.date_time_value.classArg);
              
              //setting values of selected fields when rule edit page loads.
              var value = this.getData('value');
              if(typeof value != 'undefined'){
                this.value = this.getData('value');
              }else{
                var date = new Date();
                this.value = date.toLocaleString();
                this.putData('value',this.value);
              }
              var date = new Date(this.value);
              var date_num = date.getHours();
              if(date_num >= 12){
                var hr_12 = date_num - 12;
                var day_ap = 'pm'
              }else{
                var hr_12 = date_num;
                var day_ap = 'am';
              }
              var minutes = date.getMinutes();
              if(minutes < 10){
                minutes = '0'+minutes;
              }
              var seconds = date.getSeconds();
              if(seconds < 10){
                seconds = '0'+seconds;
              }
              var Datepicker = {
                hh: hr_12,
                mm: minutes,
                ss: seconds,
                a: day_ap
              }
              this.Data = Datepicker;
            }
        };
        this.props = { emitter, ikey: key, readonly };
    }
}
