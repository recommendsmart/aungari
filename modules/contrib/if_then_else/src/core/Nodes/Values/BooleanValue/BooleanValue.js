// Defining Vue controler for Condition node.
// create it using their own modules.
var BooleanValueInputs = {
  components: { // Component included for Multiselect.
    Multiselect: window.VueMultiselect.default
  },
  props: ['readonly', 'emitter', 'ikey', 'getData', 'putData'],
  template: `<div class="fields-container">
    <div class="radio">
        <input type="checkbox" value="bool" v-model="selection" @change="selectionChanged">
        <label>Boolean</label>
    </div>   
</div>`,
  data() {
    return {
      type: drupalSettings.if_then_else.nodes.compare_string_inputs.type,
      class: drupalSettings.if_then_else.nodes.compare_string_inputs.class,
      name: drupalSettings.if_then_else.nodes.compare_string_inputs.name,
      selection: false,
    }
  },
  methods: {
    selectionChanged(value){
      this.putData('selection', this.selection);
      editor.trigger('process');
    }
  },
  mounted() {
    //Triggered when loading retejs editor. See documentaion of Vuejs

    //initialize variable for data
    this.putData('type', drupalSettings.if_then_else.nodes.boolean_value.type);
    this.putData('class', drupalSettings.if_then_else.nodes.boolean_value.class);
    this.putData('name', drupalSettings.if_then_else.nodes.boolean_value.name);

    this.selection = this.getData('selection');
  }
}

class BooleanValueControl extends Rete.Control {

  constructor(emitter, key, readonly) {
    super(key);
    this.component = BooleanValueInputs;
    this.props = { emitter, ikey: key, readonly };
  }
}
