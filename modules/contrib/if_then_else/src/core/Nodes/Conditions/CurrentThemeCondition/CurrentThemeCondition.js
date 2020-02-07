// Defining Vue controler for Condition node.
// create it using their own modules.
var VueCurrentThemeCondition = {
  components: { // Component included for Multiselect.
    Multiselect: window.VueMultiselect.default
  },
  props: ['readonly', 'emitter', 'ikey', 'getData', 'putData'],
  template: `<div class="fields-container">
  <div class="entity-select">
    <label class="typo__label">Theme</label>
    <multiselect @wheel.native.stop="wheel" v-model="value" :options="options" @input="fieldValueChanged" label="name" track-by="code" 
    :searchable="false" :close-on-select="true" :show-labels="false" placeholder="Select theme">
    </multiselect>      
  </div>
</div>`,
  data() {
    return {
      type: drupalSettings.if_then_else.nodes.current_theme_condition.type,
      class: drupalSettings.if_then_else.nodes.current_theme_condition.class,
      name: drupalSettings.if_then_else.nodes.current_theme_condition.name,
      classArg: drupalSettings.if_then_else.nodes.current_theme_condition.classArg,
      options: [],
      selected_theme: [],
      value: [],
    }
  },
  methods: {
    fieldValueChanged(value) {
      //Triggered when selecting an field.
      var selectedOptions = [];
      selectedOptions.push({
        name: value.name,
        code: value.code
      });

      this.putData('selected_theme', selectedOptions);
      editor.trigger('process');
    },
  },
  mounted() {
    //Triggered when loading retejs editor. See documentaion of Vuejs

    //initialize variable for data
    this.putData('type', drupalSettings.if_then_else.nodes.current_theme_condition.type);
    this.putData('class', drupalSettings.if_then_else.nodes.current_theme_condition.class);
    this.putData('name', drupalSettings.if_then_else.nodes.current_theme_condition.name);
    this.putData('classArg', drupalSettings.if_then_else.nodes.current_theme_condition.classArg);
    
    //setting values of selected compare option when rule edit page loads.
    var get_selected_theme = this.getData('selected_theme');
    if (typeof get_selected_theme != 'undefined') {
      this.value = this.getData('selected_theme');
    } else {
      this.putData('selected_theme', []);
    }

  },
  created() {
    if (drupalSettings.if_then_else.nodes.current_theme_condition.compare_options) {
      //setting list of all fields for a form when rule edit page loads.
      this.options = drupalSettings.if_then_else.nodes.current_theme_condition.compare_options;
    }
  }
}

class CurrentThemeConditionControl extends Rete.Control {

  constructor(emitter, key, readonly) {
    super(key);
    this.component = VueCurrentThemeCondition;
    this.props = {
      emitter,
      ikey: key,
      readonly
    };
  }
}
