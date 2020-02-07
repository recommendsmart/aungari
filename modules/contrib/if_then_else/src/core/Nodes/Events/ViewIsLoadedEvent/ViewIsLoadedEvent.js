class ViewIsLoadedEventControl extends Rete.Control {

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
            <label class="typo__label">View Name</label>
            <multiselect @wheel.native.stop="wheel" v-model="selected_view_name" :show-labels="false" :options="views" 
            placeholder="View Name" @input="viewNameSelected" label="label" 
            track-by="value"></multiselect>
          </div>
            
          <div class="bundle-select" v-if="showDisplayList">    
            <label class="typo__label">Display Id</label>
            <multiselect @wheel.native.stop="wheel" v-model="selected_display_id" :options="displays" :show-labels="false" 
            placeholder="Display Id" @input="displayIdSelected" label="label" 
            track-by="value"></multiselect>
          </div>
        </div>`,
      data() {
        return {
          type: drupalSettings.if_then_else.nodes.view_is_loaded_event.type,
          class: drupalSettings.if_then_else.nodes.view_is_loaded_event.class,
          name: drupalSettings.if_then_else.nodes.view_is_loaded_event.name,
          classArg: drupalSettings.if_then_else.nodes.view_is_loaded_event.classArg,
          value: 0,
          showDisplayList: true,
          views: [],
          displays: [],
          selected_view_name: [],
          selected_display_id: [],
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
        viewNameSelected(value) {
          //Triggered when selecting an entity from entity dropdown.
          //reinitialize all values
          this.displays = [];
          this.selected_display_id = [];
          this.displayIdSelected();
          this.selected_view_name = [];
          if (value !== null) { //check if an entity is selected
            let entity_id = value.value;
            this.selected_view_name = {
              label: value.label,
              value: value.value
            };
            //This value is passed from module.
            let bundle_list = drupalSettings.if_then_else.nodes.view_is_loaded_event.entity_info[entity_id]['display'];
            this.showDisplayList = true;

            Object.keys(bundle_list).forEach(itemKey => {
              this.displays.push({
                label: bundle_list[itemKey].label,
                value: bundle_list[itemKey].id
              });
            });
          }

          //Updating reactive variable of Vue to reflect changes on frontend
          this.putData('selected_display_id', []);
          this.putData('selected_view_name', this.selected_view_name);
          editor.trigger('process');
        },
        displayIdSelected() {
          //Triggered when a bundle is selected. We are fetching fields using ajax in this function
          this.showLoadingSpinner = false;

          this.putData('selected_display_id', this.selected_display_id);
          editor.trigger('process');
        }
      },
      mounted() {
        //Triggered when loading retejs editor. See documentaion of Vuejs

        //initialize variable for data
        this.putData('type', drupalSettings.if_then_else.nodes.view_is_loaded_event.type);
        this.putData('class', drupalSettings.if_then_else.nodes.view_is_loaded_event.class);
        this.putData('name', drupalSettings.if_then_else.nodes.view_is_loaded_event.name);
        this.putData('classArg', drupalSettings.if_then_else.nodes.view_is_loaded_event.classArg);
        
        //Setting values of retejs condition nodes when editing rule page loads
        this.selected_view_name = this.getData('selected_view_name');
        this.selected_display_id = this.getData('selected_display_id');

      },
      created() {
        //Triggered when loading retejs editor but before mounted function. See documentaion of Vuejs

        //Fetching values of fields when editing rule page loads
        if (drupalSettings.if_then_else.nodes.view_is_loaded_event.entity_info) {
          var views_list = drupalSettings.if_then_else.nodes.view_is_loaded_event.entity_info;
          Object.keys(views_list).forEach(itemKey => {
            this.views.push({
              label: views_list[itemKey].label,
              value: views_list[itemKey].id
            });
          });

          // Load the bundle list when form loads for edit
          this.selected_view_name = this.getData('selected_view_name');
          if (this.selected_view_name != undefined && typeof this.selected_view_name != 'undefined' && this.selected_view_name !== '') {
            let selected_view_name = this.selected_view_name.value;
            if (drupalSettings.if_then_else.nodes.view_is_loaded_event.entity_info) {
              let bundle_list = drupalSettings.if_then_else.nodes.view_is_loaded_event.entity_info[selected_view_name]['display'];
              Object.keys(bundle_list).forEach(itemKey => {
                this.displays.push({
                  label: bundle_list[itemKey].label,
                  value: bundle_list[itemKey].id
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
