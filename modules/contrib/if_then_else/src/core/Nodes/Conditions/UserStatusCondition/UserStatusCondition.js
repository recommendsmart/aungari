// Defining Vue controler for Condition node.
// create it using their own modules.
var VueUserStatusCondition = {
  components: { // Component included for Multiselect.
    Multiselect: window.VueMultiselect.default
  },
  props: ['readonly', 'emitter', 'ikey', 'getData', 'putData'],
  template: `<div class="fields-container">
  <div class="entity-select">
    <label class="typo__label">Status</label>
    <multiselect @wheel.native.stop="wheel" v-model="selected_status" :options="options" @input="fieldValueChanged" label="name" track-by="code" 
    :searchable="false" :close-on-select="true" :show-labels="false" placeholder="Select status">
    </multiselect>
  </div>    
</div>`,
  data() {
    return {
      type: drupalSettings.if_then_else.nodes.user_status_condition.type,
      class: drupalSettings.if_then_else.nodes.user_status_condition.class,
      name: drupalSettings.if_then_else.nodes.user_status_condition.name,
      options: [],
      selected_status: [],
    }
  },
  methods: {
    fieldValueChanged(value) {
      //Triggered when selecting an field.
      this.selected_status = [];
      if (value !== null) { //check if an entity is selected
        this.selected_status = {
          name: value.name,
          code: value.code
        };
      }
      this.putData('selected_status', this.selected_status);
      editor.trigger('process');
    },
  },
  mounted() {
    //Triggered when loading retejs editor. See documentaion of Vuejs

    //initialize variable for data
    this.putData('type', drupalSettings.if_then_else.nodes.user_status_condition.type);
    this.putData('class', drupalSettings.if_then_else.nodes.user_status_condition.class);
    this.putData('name', drupalSettings.if_then_else.nodes.user_status_condition.name);

    //setting values of selected compare option when rule edit page loads.
    var get_selected_status = this.getData('selected_status');
    if (typeof get_selected_status != 'undefined') {
      this.selected_status = get_selected_status;
    } else {
      this.putData('selected_status', []);
    }
  },
  created() {
    if (drupalSettings.if_then_else.nodes.user_status_condition.compare_options) {
      //setting list of all fields for a form when rule edit page loads.
      this.options = drupalSettings.if_then_else.nodes.user_status_condition.compare_options;
    }
  }
}

class UserStatusConditionControl extends Rete.Control {

  constructor(emitter, key, readonly) {
    super(key);
    this.component = VueUserStatusCondition;
    this.props = {
      emitter,
      ikey: key,
      readonly
    };
  }
}
