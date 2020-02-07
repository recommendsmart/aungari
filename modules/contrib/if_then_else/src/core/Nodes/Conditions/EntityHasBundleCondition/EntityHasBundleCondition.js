class EntityHasBundleConditionControl extends Rete.Control {

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
          <div class="entity-select">
            <label class="typo__label">Type</label>
            <multiselect @wheel.native.stop="wheel" v-model="selected_entity" :show-labels="false" :options="entities" 
            placeholder="Entity" @input="entitySelected" label="label" 
            track-by="value"></multiselect>
          </div>
            
          <div class="bundle-select" v-if="showBundleList">    
            <label class="typo__label">Bundle</label>
            <multiselect @wheel.native.stop="wheel" v-model="selected_bundle" :options="bundles" :show-labels="false" 
            placeholder="Bundle" @input="bundleSelected" label="label" 
            track-by="value"></multiselect>
          </div>
        </div>`,
      data() {
        return {
          type: drupalSettings.if_then_else.nodes.entity_has_bundle_condition.type,
          class: drupalSettings.if_then_else.nodes.entity_has_bundle_condition.class,
          name: drupalSettings.if_then_else.nodes.entity_has_bundle_condition.name,
          classArg: drupalSettings.if_then_else.nodes.entity_has_bundle_condition.classArg,
          value: 0,
          showBundleList: true,
          entities: [],
          bundles: [],
          selection: 'list',
          selected_entity: [],
          selected_bundle: [],
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
          this.bundleSelected();
          this.selected_entity = [];
          if (value !== null) { //check if an entity is selected
            let entity_id = value.value;
            this.selected_entity = {
              label: value.label,
              value: value.value
            };
            //This value is passed from module.
            let bundle_list = drupalSettings.if_then_else.nodes.entity_has_bundle_condition.entity_info[entity_id]['bundles'];
            this.showBundleList = true;

            Object.keys(bundle_list).forEach(itemKey => {
              this.bundles.push({
                label: bundle_list[itemKey].label,
                value: bundle_list[itemKey].bundle_id
              });
            });
          }

          //Updating reactive variable of Vue to reflect changes on frontend
          this.putData('selected_bundle', []);
          this.putData('selected_entity', this.selected_entity);
          editor.trigger('process');
        },
        bundleSelected() {
          //Triggered when a bundle is selected. We are fetching fields using ajax in this function
          this.showLoadingSpinner = false;

          this.putData('selected_bundle', this.selected_bundle);
          editor.trigger('process');
        },
        selectionChanged() {
          this.putData('selection', this.selection);
          editor.trigger('process');
        }
      },
      mounted() {
        //Triggered when loading retejs editor. See documentaion of Vuejs

        //initialize variable for data
        this.putData('type', drupalSettings.if_then_else.nodes.entity_has_bundle_condition.type);
        this.putData('class', drupalSettings.if_then_else.nodes.entity_has_bundle_condition.class);
        this.putData('name', drupalSettings.if_then_else.nodes.entity_has_bundle_condition.name);
        this.putData('classArg', drupalSettings.if_then_else.nodes.entity_has_bundle_condition.classArg);
        
        //Setting values of retejs condition nodes when editing rule page loads
        this.selected_entity = this.getData('selected_entity');
        this.selected_bundle = this.getData('selected_bundle');

        this.selection = this.getData('selection');
      },
      created() {
        //Triggered when loading retejs editor but before mounted function. See documentaion of Vuejs

        //Fetching values of fields when editing rule page loads
        if (drupalSettings.if_then_else.nodes.entity_has_bundle_condition.entity_info) {
          var entities_list = drupalSettings.if_then_else.nodes.entity_has_bundle_condition.entity_info;
          Object.keys(entities_list).forEach(itemKey => {
            this.entities.push({
              label: entities_list[itemKey].label,
              value: entities_list[itemKey].entity_id
            });
          });

          // Load the bundle list when form loads for edit
          this.selected_entity = this.getData('selected_entity');
          if (this.selected_entity != undefined && typeof this.selected_entity != 'undefined' && this.selected_entity !== '') {
            let selected_entity = this.selected_entity.value;
            if (drupalSettings.if_then_else.nodes.entity_has_bundle_condition.entity_info) {
              let bundle_list = drupalSettings.if_then_else.nodes.entity_has_bundle_condition.entity_info[selected_entity]['bundles'];
              Object.keys(bundle_list).forEach(itemKey => {
                this.bundles.push({
                  label: bundle_list[itemKey].label,
                  value: bundle_list[itemKey].bundle_id
                });
              });
            }
          }
        }
      }
    };
    this.props = {
      emitter,
      ikey: key,
      readonly
    };
  }

  setValue(val) {
    this.vueContext.value = val;
  }
}
