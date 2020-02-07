//Vuejs control for action node of making fields required.
var VueInitEventControl = {
  props: ['emitter', 'ikey', 'getData', 'putData'],
  components: {
    Multiselect: window.VueMultiselect.default
  },
  template: `
    <div class="fields-container">
      <div class="label">Match Condition</div>   
      <div class="radio">
        <input type="radio" :id="radio1_uid" value="other" v-model="form_selection" @change="formSelectionChanged">
        <label :for="radio1_uid">Enter specific page path</label>
      </div>          
      <div class="form-fields-selection" v-if="form_selection === 'other'">
        <label class="typo__label">Path To Match With</label>      
        <input type="text" v-model='valueText' @blur="valueTextChanged" placeholder="Enter path" />
      </div>
      <div class="radio">
        <input type="radio" :id="radio2_uid" value="all" v-model="form_selection" @change="formSelectionChanged">
        <label :for="radio2_uid">All Pages</label>
      </div>      
    </div>`,
  data() {
    return {
      type: drupalSettings.if_then_else.nodes.init_event.type,
      class: drupalSettings.if_then_else.nodes.init_event.class,
      name: drupalSettings.if_then_else.nodes.init_event.name,
      valueText: '',
      form_selection: 'other',
      radio1_uid: '',
      radio2_uid: '',
    }
  },
  methods: {
    update() {
      //This is called to reprocess the retejs editor
      this.emitter.trigger('process');
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
    //initialize variable for data
    this.putData('type', drupalSettings.if_then_else.nodes.init_event.type);
    this.putData('class', drupalSettings.if_then_else.nodes.init_event.class);
    this.putData('name', drupalSettings.if_then_else.nodes.init_event.name);

    this.valueText = this.getData('valueText');
    this.form_selection = this.getData('form_selection');
  },
  created() {
    this.radio1_uid = _.uniqueId('radio_');
    this.radio2_uid = _.uniqueId('radio_');
  }
};

class InitEventControl extends Rete.Control {
  constructor(emitter, key) {
    super(key);
    this.component = VueInitEventControl;
    this.props = {
      emitter,
      ikey: key
    };
  }
}
