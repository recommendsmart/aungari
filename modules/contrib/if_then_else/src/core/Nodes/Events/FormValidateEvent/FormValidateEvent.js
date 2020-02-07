class FormValidateEventControl extends Rete.Control {

  constructor(emitter, key, readonly) {
    super(key);
    this.component = {
      components: {
        // Component included for Multiselect.
        Multiselect: window.VueMultiselect.default
      },
      props: ['readonly', 'emitter', 'ikey', 'getData', 'putData'],
      template: `
    <div class="fields-container">
      <div class="label">Match Condition</div>

      <div class="radio">
        <input type="radio" :id="radio1_uid" value="list" v-model="form_selection" @change="formSelectionChanged">
        <label :for="radio1_uid">Select An Entity Form</label>
      </div>
      
      <div v-if="form_selection === 'list'">
        <div class="entity-select">
          <label class="typo__label">Entity</label>
          <multiselect @wheel.native.stop="wheel" v-model="selected_entity" :show-labels="false" :options="entities" 
          placeholder="Entity" @input="entitySelected" label="label" 
          track-by="value"></multiselect></div><div class="bundle-select" v-if="showBundleList">
        
          <label class="typo__label">Bundle</label>
          <multiselect @wheel.native.stop="wheel" v-model="selected_bundle" :options="bundles" :show-labels="false" 
          placeholder="Bundle" @input="bundleSelected" label="label" 
          track-by="value"></multiselect>
        </div>
      </div>

      <div class="radio">
        <input type="radio" :id="radio2_uid" value="other" v-model="form_selection" @change="formSelectionChanged">
        <label :for="radio2_uid">Enter Form Class Name</label>
      </div>
      
      <div class="other-form-field" v-if="form_selection === 'other'" >
        <input type="text" name="other-form-class" :value="otherFormClass" placeholder="e.g. \\Drupal\\Node\\Form"
        @blur="updateOtherFormClass" />
      </div>
      
      <div class="radio">
        <input type="radio" :id="radio3_uid" value="all" v-model="form_selection" @change="formSelectionChanged">
        <label :for="radio3_uid">All Forms</label>
      </div>

    </div>`,
      data() {
        return {
          type: drupalSettings.if_then_else.nodes.form_validate_event.type,
          class: drupalSettings.if_then_else.nodes.form_validate_event.class,
          name: drupalSettings.if_then_else.nodes.form_validate_event.name,
          classArg: drupalSettings.if_then_else.nodes.form_validate_event.classArg,
          value: 0,
          otherFormClass: '',
          showOtherFormField: false,
          showBundleList: true,
          entities: [],
          bundles: [],
          form_selection: 'list',
          selected_entity: [],
          selected_bundle: [],
          radio1_uid: '',
          radio2_uid: '',
          radio3_uid: '',
        }
      },
      methods: {
        update() {
          //Triggered on focus out of formclass input field
          if (this.ikey)
            this.putData(this.ikey, this.value);

          //This is called to reprocess the retejs editor
          editor.trigger('process');
        },
        entitySelected(value) {
          //Triggered when selecting an entity from entity dropdown.
          //reinitialize all values
          this.bundles = [];
          this.selected_bundle = [];
          this.otherFormClass = '';
          this.bundleSelected();
          this.selected_entity = [];
          if (value !== null){ //check if an entity is selected
            let entity_id = value.value;
            this.selected_entity = {label: value.label, value: value.value};
            //This value is passed from module.
            let bundle_list = drupalSettings.if_then_else.nodes.form_validate_event.entity_info[entity_id]['bundles'];
            this.showBundleList = true;
            this.showOtherFormField = false;

            Object.keys(bundle_list).forEach(itemKey => {
              this.bundles.push({label: bundle_list[itemKey].label, value: bundle_list[itemKey].bundle_id});
            });
          }

          //Updating reactive variable of Vue to reflect changes on frontend
          this.putData('selected_bundle',[]);
          this.putData('selected_entity',this.selected_entity);
          editor.trigger('process');
        },
        bundleSelected(){
          //Triggered when a bundle is selected. We are fetching fields using ajax in this function
          this.showLoadingSpinner = false;

          this.putData('selected_bundle',this.selected_bundle);
          editor.trigger('process');
        },
        formSelectionChanged() {
          this.putData('form_selection', this.form_selection);
          editor.trigger('process');
        },
        updateOtherFormClass(e){
          //Triggered when entering form class
          this.putData('otherFormClass',e.target.value);
          editor.trigger('process');
        }
      },
      mounted() {
        //Triggered when loading retejs editor. See documentaion of Vuejs

        //initialize variable for data
        this.putData('type', drupalSettings.if_then_else.nodes.form_validate_event.type);
        this.putData('class', drupalSettings.if_then_else.nodes.form_validate_event.class);
        this.putData('name', drupalSettings.if_then_else.nodes.form_validate_event.name);
        this.putData('classArg', drupalSettings.if_then_else.nodes.form_validate_event.classArg);
        
        //Setting values of retejs condition nodes when editing rule page loads
        this.selected_entity = this.getData('selected_entity');
        this.selected_bundle = this.getData('selected_bundle');
        this.otherFormClass = this.getData('otherFormClass');

        this.form_selection = this.getData('form_selection');
      },
      created() {
        //Triggered when loading retejs editor but before mounted function. See documentaion of Vuejs.
        this.radio1_uid = _.uniqueId('radio_');
        this.radio2_uid = _.uniqueId('radio_');
        this.radio3_uid = _.uniqueId('radio_');

        //Fetching values of fields when editing rule page loads
        if(drupalSettings.if_then_else.nodes.form_validate_event.entity_info){
          var entities_list = drupalSettings.if_then_else.nodes.form_validate_event.entity_info;
          Object.keys(entities_list).forEach(itemKey => {
            this.entities.push({label: entities_list[itemKey].label, value: entities_list[itemKey].entity_id});
          });
          //this.entities.push({label: 'Other Form', value:	'other_form' });

          // Load the bundle list when form loads for edit
          this.selected_entity = this.getData('selected_entity');
          if(this.selected_entity != undefined && typeof this.selected_entity != 'undefined' && this.selected_entity !== ''){
            var selected_entity = this.selected_entity.value;
            if(drupalSettings.if_then_else.nodes.form_validate_event.entity_info){
              if(selected_entity !== 'other_form'){
                var bundle_list = drupalSettings.if_then_else.nodes.form_validate_event.entity_info[selected_entity]['bundles'];
                Object.keys(bundle_list).forEach(itemKey => {
                  this.bundles.push({label: bundle_list[itemKey].label, value: bundle_list[itemKey].bundle_id});
                });
              }
            }
          }

        }
      }
    };
    this.props = { emitter, ikey: key, readonly };
  }

  setValue(val) {
    this.vueContext.value = val;
  }
}
