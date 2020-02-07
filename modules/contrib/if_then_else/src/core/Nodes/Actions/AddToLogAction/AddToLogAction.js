// Defining Vue controler for action node.
// create it using their own modules.
var VueAddToLogAction = {
  components: { // Component included for Multiselect.
    Multiselect: window.VueMultiselect.default
  },
  props: ['readonly', 'emitter', 'ikey', 'getData', 'putData'],
  template: `<div class="fields-container">
  <div class="entity-select">
    <label class="typo__label">Severity</label>
    <multiselect @wheel.native.stop="wheel" v-model="selected_severity" :options="options" @input="fieldValueChanged" label="name" track-by="code" 
    :searchable="false" :close-on-select="true" :show-labels="false" placeholder="Select Severity">
    </multiselect>
  </div>    
</div>`,
  data() {
    return {
      type: drupalSettings.if_then_else.nodes.add_to_log_action.type,
      class: drupalSettings.if_then_else.nodes.add_to_log_action.class,
      name: drupalSettings.if_then_else.nodes.add_to_log_action.name,
      classArg: drupalSettings.if_then_else.nodes.add_to_log_action.classArg,
      options: [],
      selected_severity: [],
    }
  },
  methods: {
    fieldValueChanged(value) {
      //Triggered when selecting a severity.
      this.selected_severity = [];
      if (value !== null) { //check if an severity is selected
        this.selected_severity = {
          name: value.name,
          code: value.code
        };
      }
      this.putData('selected_severity', this.selected_severity);
      editor.trigger('process');
    },
  },
  mounted() {
    //Triggered when loading retejs editor. See documentaion of Vuejs

    //initialize variable for data
    this.putData('type', drupalSettings.if_then_else.nodes.add_to_log_action.type);
    this.putData('class', drupalSettings.if_then_else.nodes.add_to_log_action.class);
    this.putData('name', drupalSettings.if_then_else.nodes.add_to_log_action.name);
    this.putData('classArg', drupalSettings.if_then_else.nodes.add_to_log_action.classArg);

    //setting values of selected severity option when rule edit page loads.
    var get_selected_severity = this.getData('selected_severity');
    if (typeof get_selected_severity != 'undefined') {
      this.selected_severity = get_selected_severity;
    } else {
      this.putData('selected_severity', []);
    }
  },
  created() {
    if (drupalSettings.if_then_else.nodes.add_to_log_action.compare_options) {
      //setting list of severity for a form when rule edit page loads.
      this.options = drupalSettings.if_then_else.nodes.add_to_log_action.compare_options;
    }
  }
}

class AddToLogActionControl extends Rete.Control {

  constructor(emitter, key, readonly) {
    super(key);
    this.component = VueAddToLogAction;
    this.props = {
      emitter,
      ikey: key,
      readonly
    };
  }
}
