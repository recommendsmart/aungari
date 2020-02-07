// Defining Vue controler for action node.
// create it using their own modules.
var VueSendAccountEmailAction = {
  components: { // Component included for Multiselect.
    Multiselect: window.VueMultiselect.default
  },
  props: ['readonly', 'emitter', 'ikey', 'getData', 'putData'],
  template: `<div class="fields-container">
  <div class="entity-select">
    <label class="typo__label">Email Type</label>
    <multiselect @wheel.native.stop="wheel" v-model="selected_type" :options="options" @input="fieldValueChanged" label="name" track-by="code" 
    :searchable="false" :close-on-select="true" :show-labels="false" placeholder="Select Email Type">
    </multiselect>
  </div>    
</div>`,
  data() {
    return {
      type: drupalSettings.if_then_else.nodes.send_account_email_action.type,
      class: drupalSettings.if_then_else.nodes.send_account_email_action.class,
      name: drupalSettings.if_then_else.nodes.send_account_email_action.name,
      classArg: drupalSettings.if_then_else.nodes.send_account_email_action.classArg,
      options: [],
      selected_type: [],
    }
  },
  methods: {
    fieldValueChanged(value) {
      //Triggered when selecting an field.
      this.selected_type = [];
      if (value !== null) { //check if an entity is selected
        this.selected_type = {
          name: value.name,
          code: value.code
        };
      }
      this.putData('selected_type', this.selected_type);
      editor.trigger('process');
    },
  },
  mounted() {
    //Triggered when loading retejs editor. See documentaion of Vuejs

    //initialize variable for data
    this.putData('type', drupalSettings.if_then_else.nodes.send_account_email_action.type);
    this.putData('class', drupalSettings.if_then_else.nodes.send_account_email_action.class);
    this.putData('name', drupalSettings.if_then_else.nodes.send_account_email_action.name);
    this.putData('classArg', drupalSettings.if_then_else.nodes.send_account_email_action.classArg);

    //setting values of selected compare option when rule edit page loads.
    var get_selected_type = this.getData('selected_type');
    if (typeof get_selected_type != 'undefined') {
      this.selected_type = get_selected_type;
    } else {
      this.putData('selected_type', []);
    }
  },
  created() {
    if (drupalSettings.if_then_else.nodes.send_account_email_action.compare_options) {
      //setting list of all fields for a form when rule edit page loads.
      this.options = drupalSettings.if_then_else.nodes.send_account_email_action.compare_options;
    }
  }
}

class SendAccountEmailActionControl extends Rete.Control {

  constructor(emitter, key, readonly) {
    super(key);
    this.component = VueSendAccountEmailAction;
    this.props = {
      emitter,
      ikey: key,
      readonly
    };
  }
}
