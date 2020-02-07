//Vuejs control for action node of making fields required.
var VueSetVariableActionControl = {
  props: ['emitter', 'ikey', 'getData', 'putData'],
  components: {
    Multiselect: window.VueMultiselect.default
  },
  template: `
    <div class="fields-container">
      <div class="form-fields-selection" >
        <label class="typo__label">Config Name</label>      
        <input type="text" v-model='valueText' @blur="valueTextChanged" placeholder="Enter config name" />
      </div>
    </div>`,
  data() {
    return {
      type: drupalSettings.if_then_else.nodes.set_variable_action.type,
      class: drupalSettings.if_then_else.nodes.set_variable_action.class,
      name: drupalSettings.if_then_else.nodes.set_variable_action.name,
      classArg: drupalSettings.if_then_else.nodes.set_variable_action.classArg,
      valueText: '',
    }
  },
  methods: {
    update() {
      //This is called to reprocess the retejs editor
      this.emitter.trigger('process');
    },
    valueTextChanged(){
      this.putData('valueText',this.valueText);
      editor.trigger('process');
    }
  },
  mounted() {
    //initialize variable for data
    this.putData('type',drupalSettings.if_then_else.nodes.set_variable_action.type);
    this.putData('class',drupalSettings.if_then_else.nodes.set_variable_action.class);
    this.putData('name', drupalSettings.if_then_else.nodes.set_variable_action.name);
    this.putData('classArg', drupalSettings.if_then_else.nodes.set_variable_action.classArg);
    
    this.valueText = this.getData('valueText');

  },
};

class SetVariableActionControl extends Rete.Control {
  constructor(emitter, key) {
    super(key);
    this.component = VueSetVariableActionControl;
    this.props = { emitter, ikey: key };
  }
}
