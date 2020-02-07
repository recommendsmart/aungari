// Defining Vue controler for Condition node.
// create it using their own modules.
var VueFormIdConditionOrOfAllTheInputs = {
  components: {  },
  props: ['readonly', 'emitter', 'ikey', 'getData', 'putData'],
  template: ``,
  data() {
    return {
      type: drupalSettings.if_then_else.nodes.condition_or_of_all_the_inputs.type,
      class: drupalSettings.if_then_else.nodes.condition_or_of_all_the_inputs.class,
      name: drupalSettings.if_then_else.nodes.condition_or_of_all_the_inputs.name,
    }
  },
  methods: {},
  mounted() {
    //Triggered when loading retejs editor. See documentaion of Vuejs

    //initialize variable for data
    this.putData('type', drupalSettings.if_then_else.nodes.condition_or_of_all_the_inputs.type);
    this.putData('class', drupalSettings.if_then_else.nodes.condition_or_of_all_the_inputs.class);
    this.putData('name', drupalSettings.if_then_else.nodes.condition_or_of_all_the_inputs.name);
  },
  created() {
  }
}

class FormIdConditionOrOfAllTheInputs extends Rete.Control {

  constructor(emitter, key, readonly) {
    super(key);
    this.component = VueFormIdConditionOrOfAllTheInputs;
    this.props = { emitter, ikey: key, readonly };
  }

  setValue(val) {
    this.vueContext.value = val;
  }
}