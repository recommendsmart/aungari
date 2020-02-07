// Defining Vue controler for Condition node.
// create it using their own modules.
var VuePeriodicExecutionCondition = {
  components: { // Component included for Multiselect.
    Multiselect: window.VueMultiselect.default
  },
  props: ['readonly', 'emitter', 'ikey', 'getData', 'putData'],
  template: `<div class="fields-container">
  <div class="label">Run Every</div>
    <div class="radio">
      <input type="radio" :id="radio1_uid" value="list" v-model="form_selection" @change="formSelectionChanged">
      <label :for="radio1_uid">Select An Hour</label>
    </div>  
    <div class="entity-select" v-if="form_selection === 'list'">
      <multiselect @wheel.native.stop="wheel" v-model="selected_option" :options="options" @input="fieldValueChanged" label="name" track-by="code" 
      :searchable="false" :close-on-select="true" :show-labels="false" placeholder="Select compare operation">
      </multiselect>
    </div>    
    <div class="radio">
       <input type="radio" :id="radio2_uid" value="other" v-model="form_selection" @change="formSelectionChanged">
       <label :for="radio2_uid">Input Custom Hours</label>
    </div>    
    <div class="custom-field form-item" v-if="form_selection == 'other'">
      <input type="text" v-model='valueText' @blur="valueTextChanged" placeholder="Enter custom hour"  />
    </div>   
</div>`,
  data() {
    return {
      type: drupalSettings.if_then_else.nodes.periodic_execution_condition.type,
      class: drupalSettings.if_then_else.nodes.periodic_execution_condition.class,
      name: drupalSettings.if_then_else.nodes.periodic_execution_condition.name,
      classArg: drupalSettings.if_then_else.nodes.periodic_execution_condition.classArg,
      options: [],
      form_selection: 'list',
      valueText: '',
      selected_option: [],
    }
  },
  methods: {
    fieldValueChanged(value) {
      //Triggered when selecting an field.
      this.selected_option = [];
      if (value !== null) { //check if an entity is selected
        this.selected_option = {
          name: value.name,
          code: value.code
        };
      }
      this.putData('selected_option', this.selected_option);
      editor.trigger('process');
    },
    valueTextChanged() {
      this.putData('valueText', this.valueText);
      editor.trigger('process');
    },
    formSelectionChanged() {
      this.putData('form_selection', this.form_selection);
      editor.trigger('process');
    },
  },
  mounted() {
    //Triggered when loading retejs editor. See documentaion of Vuejs

    //initialize variable for data
    this.putData('type', drupalSettings.if_then_else.nodes.periodic_execution_condition.type);
    this.putData('class', drupalSettings.if_then_else.nodes.periodic_execution_condition.class);
    this.putData('name', drupalSettings.if_then_else.nodes.periodic_execution_condition.name);
    this.putData('classArg', drupalSettings.if_then_else.nodes.periodic_execution_condition.classArg);
      
    //setting values of selected compare option when rule edit page loads.
    var get_selected_option = this.getData('selected_option');
    if (typeof get_selected_option != 'undefined') {
      this.selected_option = get_selected_option;
    } else {
      this.putData('selected_option', []);
    }
    this.valueText = this.getData('valueText');
    this.form_selection = this.getData('form_selection');
  },
  created() {
    this.radio1_uid = _.uniqueId('radio_');
    this.radio2_uid = _.uniqueId('radio_');
    if (drupalSettings.if_then_else.nodes.periodic_execution_condition.compare_options) {
      //setting list of all fields for a form when rule edit page loads.
      this.options = drupalSettings.if_then_else.nodes.periodic_execution_condition.compare_options;
    }
  }
}

class PeriodicExecutionConditionControl extends Rete.Control {

  constructor(emitter, key, readonly) {
    super(key);
    this.component = VuePeriodicExecutionCondition;
    this.props = {
      emitter,
      ikey: key,
      readonly
    };
  }
}
