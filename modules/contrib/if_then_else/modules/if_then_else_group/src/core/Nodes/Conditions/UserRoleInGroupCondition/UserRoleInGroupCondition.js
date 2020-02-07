var VueUserRoleInGroupConditionControl = {
  props: ['emitter', 'ikey', 'getData', 'putData', 'onChange'],
  components: {
    Multiselect: window.VueMultiselect.default
  },
  data() {
    return {
      type: drupalSettings.if_then_else.nodes.user_role_in_group_condition.type,
      class: drupalSettings.if_then_else.nodes.user_role_in_group_condition.class,
      name: drupalSettings.if_then_else.nodes.user_role_in_group_condition.name,
      classArg: drupalSettings.if_then_else.nodes.user_role_in_group_condition.classArg,
      value: [],
      options: [],
      selected_options: [],
      group_roles: [],
      selected_roles: [],
    }
  },
  template: `
    <div class="fields-container">
      <div class="form-fields-selection" >
      <label class="typo__label">Group Type</label>
      <multiselect @wheel.native.stop="wheel" v-model="value" :options="options" @input="fieldValueChanged" label="name" track-by="code" 
      :searchable="false" :close-on-select="true" :show-labels="false" placeholder="Select a group type">
      </multiselect>
      <label v-if="value != ''" v-model="selected_roles" class="typo__label">Group Roles</label>
      <multiselect @wheel.native.stop="wheel" v-if="value != ''" v-model="selected_roles" :options="group_roles" :multiple="false" :taggable="true" @input="entityFieldValueChanged" label="name" track-by="code" 
      :searchable="false" :close-on-select="true" :show-labels="false" placeholder="Select group roles.">
      </multiselect>
      </div>
    </div>`,
 
  methods: {
    fieldValueChanged(value){
      if(value !== undefined && value !== null && value !== ''){
        //Triggered when selecting an field.
        var selectedOptions = [];

        selectedOptions = {name: value.name, code: value.code};

        //check if selected field value is changed.
        var prevSelectedField = this.getData('selected_options');
        if(typeof prevSelectedField != 'undefined' && prevSelectedField.code != value.code){
          this.selected_roles = '';
          this.putData('selected_roles','');
        }

        var field_roles = drupalSettings.if_then_else.nodes.user_role_in_group_condition.group_roles
        this.group_roles = field_roles[value.code];
        this.putData('selected_options',selectedOptions);
        editor.trigger('process');
      }else{
        this.putData('selected_options','');
        this.putData('selected_roles','');
        this.value = '';
      }
    },
    entityFieldValueChanged(value){
      if(value !== undefined && value !== null && value !== ''){
        var selected_roles = [];
          selected_roles.push({name: value.name, code: value.code});
        this.putData('selected_roles',selected_roles);
        editor.trigger('process');
      }
    },
  },
  mounted() {
    //initialize variable for data
    this.putData('type',drupalSettings.if_then_else.nodes.user_role_in_group_condition.type);
    this.putData('class',drupalSettings.if_then_else.nodes.user_role_in_group_condition.class);
    this.putData('name', drupalSettings.if_then_else.nodes.user_role_in_group_condition.name);
    this.putData('classArg', drupalSettings.if_then_else.nodes.user_role_in_group_condition.classArg);
  
    //setting values of selected fields when rule edit page loads.
    //Setting values of retejs condition nodes when editing rule page loads
    var get_selected_options = this.getData('selected_options'); 
    var get_selected_roles = this.getData('selected_roles');
    if(typeof get_selected_options != 'undefined'){
      this.value = get_selected_options;
      var field_roles = drupalSettings.if_then_else.nodes.user_role_in_group_condition.group_roles
      this.group_roles = field_roles[get_selected_options.code];
      this.selected_roles = this.getData('selected_roles');
    }
    else {
      this.putData('selected_options',[]);
    }
  },
  created() {
    if(drupalSettings.if_then_else.nodes.user_role_in_group_condition.group_types){
      //setting list of all fields for a form when rule edit page loads.
      this.options = drupalSettings.if_then_else.nodes.user_role_in_group_condition.group_types;
    }
  } 
};
class UserRoleInGroupConditionControl extends Rete.Control {
  constructor(emitter, key, onChange) {
    super(key);
    this.component = VueUserRoleInGroupConditionControl;
    this.props = { emitter, ikey: key, onChange};
  }
}
