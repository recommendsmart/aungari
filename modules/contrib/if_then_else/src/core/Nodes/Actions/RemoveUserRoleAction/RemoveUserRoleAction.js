var VueRemoveUserRoleActionControl = {
  props: ['emitter', 'ikey', 'getData', 'putData'],
  components: {
    Multiselect: window.VueMultiselect.default
  },
  template: `
    <div class="fields-container">
      <div class="form-fields-selection" >
        <label for="one">User Roles</label>   
        <multiselect @wheel.native.stop="wheel" v-model="value" :show-labels="false" tag-placeholder="Add this as new tag" placeholder="Roles" label="label" track-by="name" :options="options" :multiple="true" :taggable="true" @input="updateSelected" @tag="addTag"></multiselect>
        <br>
      </div>
    </div>`,
  data() {
    return {
      type: drupalSettings.if_then_else.nodes.remove_user_role_action.type,
      class: drupalSettings.if_then_else.nodes.remove_user_role_action.class,
      name: drupalSettings.if_then_else.nodes.remove_user_role_action.name,
      classArg: drupalSettings.if_then_else.nodes.remove_user_role_action.classArg,
      value: [],
      options: [],
      selected_options: [],
    }
  },
  methods: {
    addTag (newTag) {
      //Multiselect tags
      const tag = {
        name: newTag,
        label: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
      };
      this.options.push(tag);
      this.value.push(tag)
    },
    updateSelected(value){
      //Triggered when changing field values
      var selectedOptions = [];
      value.forEach((resource) => {
        selectedOptions.push({name: resource.name, label: resource.label});
      });
      this.putData('selected_options',selectedOptions);
      editor.trigger('process');
    },
  },
  mounted() {
    //initialize variable for data
    this.putData('type',drupalSettings.if_then_else.nodes.remove_user_role_action.type);
    this.putData('class',drupalSettings.if_then_else.nodes.remove_user_role_action.class);
    this.putData('name', drupalSettings.if_then_else.nodes.remove_user_role_action.name);
    this.putData('classArg', drupalSettings.if_then_else.nodes.remove_user_role_action.classArg);
    
    //setting values of selected fields when rule edit page loads.
    //Setting values of retejs condition nodes when editing rule page loads
    var get_selected_options = this.getData('selected_options');
    if(typeof get_selected_options != 'undefined'){
      this.value = this.getData('selected_options');
    }
    else {
      this.putData('selected_options',[]);
    }
  },
  created() {
    if(drupalSettings.if_then_else.nodes.remove_user_role_action.roles){
      //Fetching values of fields when editing rule page loads
      for (let option in drupalSettings.if_then_else.nodes.remove_user_role_action.roles) {
        this.options.push({
          name: option,
          label: drupalSettings.if_then_else.nodes.remove_user_role_action.roles[option]
        });
      }
    }
  }
};

class RemoveUserRoleActionControl extends Rete.Control {
  constructor(emitter, key) {
    super(key);
    this.component = VueRemoveUserRoleActionControl;
    this.props = { emitter, ikey: key };
  }

  //setting list value of fields. Used when changing entity or bundle value in condition node.
  setOptions(options) {
    this.vueContext.options = options;
  }
}
