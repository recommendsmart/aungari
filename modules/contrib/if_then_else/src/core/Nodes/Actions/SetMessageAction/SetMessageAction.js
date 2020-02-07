//Vuejs control for action node of making fields required.
var VueSetMessageActionControl = {
  props: ['emitter', 'ikey', 'getData', 'putData'],
  components: {
    Multiselect: window.VueMultiselect.default
  },
  template: `
    <div class="fields-container">
      <div class="form-fields-selection" >
        <label for="one">Severity</label>
        <multiselect @wheel.native.stop="wheel" v-model="selected_options" :show-labels="false" :options="options" 
        :multiple="false" placeholder="Severity" @input="updateSelected" label="label" track-by="name"></multiselect>
      </div>
    </div>`,
  data() {
    return {
      type: drupalSettings.if_then_else.nodes.set_message_action.type,
      class: drupalSettings.if_then_else.nodes.set_message_action.class,
      name: drupalSettings.if_then_else.nodes.set_message_action.name,
      classArg: drupalSettings.if_then_else.nodes.set_message_action.classArg,
      options: [],
      severity_options: [],
      selected_options: [],
      value : {name: 0, label: 'status'}
    }
  },
  methods: {
    update() {
      //This is called to reprocess the retejs editor
      this.emitter.trigger('process');
    },
    updateSelected() {
      //Updating reactive variable of Vue to reflect changes on frontend
      this.putData('selected_options', this.selected_options);
      this.emitter.trigger('process');
    }
  },
  mounted() {
    //initialize variable for data
    this.putData('type',drupalSettings.if_then_else.nodes.set_message_action.type);
    this.putData('class',drupalSettings.if_then_else.nodes.set_message_action.class);
    this.putData('name', drupalSettings.if_then_else.nodes.set_message_action.name);
    this.putData('classArg', drupalSettings.if_then_else.nodes.set_message_action.classArg);
    
    //setting values of selected fields when rule edit page loads.
    //Setting values of retejs condition nodes when editing rule page loads
    var get_selected_options = this.getData('selected_options');
    if(typeof get_selected_options != 'undefined'){
      this.selected_options = this.getData('selected_options');
    }
    else {
      this.selected_options = this.value;
      this.putData('selected_options', this.selected_options);
    }
  },
  created() {
    if(drupalSettings.if_then_else.nodes.set_message_action.severity_options){
      //Fetching values of fields when editing rule page loads
      for (let option in drupalSettings.if_then_else.nodes.set_message_action.severity_options) {
        this.options.push({
          name: option,
          label: drupalSettings.if_then_else.nodes.set_message_action.severity_options[option]
        });
      }
    }
  }
};

class SetMessageActionControl extends Rete.Control {
  constructor(emitter, key) {
    super(key);
    this.component = VueSetMessageActionControl;
    this.props = { emitter, ikey: key };
  }

  //setting list value of fields. Used when changing entity or bundle value in condition node.
  setOptions(options) {
    this.vueContext.options = options;
  }
}