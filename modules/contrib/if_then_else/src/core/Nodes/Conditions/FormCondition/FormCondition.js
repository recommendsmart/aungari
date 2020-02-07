// Defining Vue controler for Condition node.
// create it using their own modules.
var VueFormIdControl = {
  components: {
    // Component included for Multiselect.
    Multiselect: window.VueMultiselect.default
  },
  props: ['readonly', 'emitter', 'ikey', 'getData', 'putData'],
  template: `
    <div class="fields-container">
      <div class="entity-select">
        <label class="typo__label">Entity</label>
        <multiselect  @wheel.native.stop="wheel" v-model="selected_entity" :show-labels="false" :options="entities" 
        placeholder="Entity" @input="entitySelected" label="label" 
        track-by="value"></multiselect></div><div class="bundle-select" v-if="showBundleList">
        
        <label class="typo__label">Bundle</label>
        <multiselect @wheel.native.stop="wheel" v-model="selected_bundle" :options="bundles" :show-labels="false" 
        placeholder="Bundle" @input="bundleSelected" label="label" 
        track-by="value"></multiselect>
      </div>
      
      <div class="other-form-field" v-if="showOtherFormField" >
        <label>Form Class Name</label>
        <input type="text" name="other-form-class" :value="otherFormClass" 
        @blur="updateOtherFormClass" />
      </div>
    </div>`,
  data() {
    return {
      type: drupalSettings.if_then_else.nodes.form_class_condition.type,
      class: drupalSettings.if_then_else.nodes.form_class_condition.class,
      name: drupalSettings.if_then_else.nodes.form_class_condition.name,
      classArg: drupalSettings.if_then_else.nodes.form_class_condition.classArg,
      value: 0,
      otherFormClass: '',
      showOtherFormField: false,
      showBundleList: true,
      entities: [],
      bundles: [],
      selected_entity: [],
      selected_bundle: [],
    }
  },
  methods: {
    update() {
      //Triggered on focus out of formclass input field
      if (this.ikey)
        this.putData(this.ikey, this.value)
      
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
        if (entity_id == 'other_form'){	//if selected entity type is other
          
          //Hide bundle list dropdown
          this.showBundleList = false;
          
          //show other form class input box
          this.showOtherFormField = true;
        }
        else{	//If selected entity type is some content entity
          //This value is passed from module.
          let bundle_list = drupalSettings.if_then_else.nodes.form_class_condition.entity_info[entity_id]['bundles'];
          this.showBundleList = true;
          this.showOtherFormField = false;
          
          Object.keys(bundle_list).forEach(itemKey => {
            this.bundles.push({label: bundle_list[itemKey].label, value: bundle_list[itemKey].bundle_id});
          });
        }
      }

      //Updating reactive variable of Vue to reflect changes on frontend
      this.putData('selected_bundle',[]);
      this.putData('selected_entity',this.selected_entity);
      this.putData('otherFormClass',this.otherFormClass);			
      editor.trigger('process');		
    },
    bundleSelected(){
      //Triggered when a bundle is selected. We are fetching fields using ajax in this function
      this.showLoadingSpinner = false;

      jQuery.each(editor.nodes,function(key,action_node){
        if(action_node.data.type == 'action' && typeof action_node.data.form_fields != 'undefined'){
          action_node.controls.forEach((control) => {
            // control.setOptions([]);
            control.setValue([]);
          })              
        }
      });

      this.putData('selected_bundle',this.selected_bundle);	
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
    this.putData('type', drupalSettings.if_then_else.nodes.form_class_condition.type);
    this.putData('class', drupalSettings.if_then_else.nodes.form_class_condition.class);
    this.putData('name', drupalSettings.if_then_else.nodes.form_class_condition.name);
    this.putData('classArg', drupalSettings.if_then_else.nodes.form_class_condition.classArg);
    
    //Setting values of retejs condition nodes when editing rule page loads
    this.selected_entity = this.getData('selected_entity');
    this.selected_bundle = this.getData('selected_bundle');
    this.otherFormClass = this.getData('otherFormClass');
    
    if(this.otherFormClass != ''){
      this.showBundleList = false;
      this.showOtherFormField = true;
    }
  },
  created() {
    //Triggered when loading retejs editor but before mounted function. See documentaion of Vuejs

    //Fetching values of fields when editing rule page loads
    if(drupalSettings.if_then_else.nodes.form_class_condition.entity_info){
      var entities_list = drupalSettings.if_then_else.nodes.form_class_condition.entity_info;
      Object.keys(entities_list).forEach(itemKey => {
        this.entities.push({label: entities_list[itemKey].label, value: entities_list[itemKey].entity_id});
      });
      //this.entities.push({label: 'Other Form', value:	'other_form' });

      // Load the bundle list when form loads for edit
      this.selected_entity = this.getData('selected_entity');
      if(this.selected_entity != undefined && typeof this.selected_entity != 'undefined' && this.selected_entity != ''){
        var selected_entity = this.selected_entity.value;
        if(drupalSettings.if_then_else.nodes.form_class_condition.entity_info){
          if(selected_entity != 'other_form'){
            var bundle_list = drupalSettings.if_then_else.nodes.form_class_condition.entity_info[selected_entity]['bundles'];
            Object.keys(bundle_list).forEach(itemKey => {
              this.bundles.push({label: bundle_list[itemKey].label, value: bundle_list[itemKey].bundle_id});
            });
          }
        } 
      }
      
    }
  }
}

class FormIdControl extends Rete.Control {

  constructor(emitter, key, readonly) {
    super(key);
    this.component = VueFormIdControl;
    this.props = { emitter, ikey: key, readonly };
  }

  setValue(val) {
    this.vueContext.value = val;
  }
}
