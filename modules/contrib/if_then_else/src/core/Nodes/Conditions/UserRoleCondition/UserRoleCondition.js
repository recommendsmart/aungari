class UserRoleConditionControl extends Rete.Control {

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
      <label>Match Criterion</label>
      <multiselect @wheel.native.stop="wheel" v-model="match" :show-labels="false" label="label" track-by="type" 
        :options="match_options" @input="updateMatchCriterion" :allow-empty="false"></multiselect>
      <label>Roles</label>
      <div class="radio">
        <input type="radio" :id="radio1_uid" value="list" v-model="input_selection" @change="inputSelectionChanged">
        <label :for="radio1_uid">Select From List</label>
      </div>
      <multiselect @wheel.native.stop="wheel" v-if="input_selection === 'list'" v-model="selected_roles" :show-labels="false" :options="roles" 
        :multiple="true" placeholder="Role" @input="roleSelected" label="label" track-by="name"></multiselect>
      <div class="radio">
        <input type="radio" :id="radio2_uid" value="input" v-model="input_selection" @change="inputSelectionChanged">
        <label :for="radio2_uid">Select From "Roles" Input</label>
      </div>
    </div>`,
      data() {
        return {
          type: drupalSettings.if_then_else.nodes.user_role_condition.type,
          class: drupalSettings.if_then_else.nodes.user_role_condition.class,
          name: drupalSettings.if_then_else.nodes.user_role_condition.name,
          classArg: drupalSettings.if_then_else.nodes.user_role_condition.classArg,
          match: 'all',
          match_options: [
            {label: 'All roles', type: 'all'},
            {label: 'At least one role', type: 'any'}
          ],
          roles: [],
          input_selection: 'list',
          selected_roles: []
        }
      },
      methods: {
        update() {
          //Triggered on focus out of formclass input field
          /*if (this.ikey)
            this.putData(this.ikey, this.value)*/

          //This is called to reprocess the retejs editor
          editor.trigger('process');
        },
        roleSelected(value) {
          //Triggered when selecting a role from roles dropdown.
          //reinitialize all values
          this.selected_roles = [];
          value.forEach((resource) => {
            this.selected_roles.push({label: resource.label, name: resource.name});
          });

          //Updating reactive variable of Vue to reflect changes on frontend
          this.putData('selected_roles', this.selected_roles);
          editor.trigger('process');
        },
        inputSelectionChanged() {
          this.putData('input_selection', this.input_selection);
          editor.trigger('process');
        },
        updateMatchCriterion() {
          this.putData('match', this.match);
          editor.trigger('process');
        }
      },
      mounted() {
        //Triggered when loading retejs editor. See documentaion of Vuejs

        //initialize variable for data
        this.putData('type', drupalSettings.if_then_else.nodes.user_role_condition.type);
        this.putData('class', drupalSettings.if_then_else.nodes.user_role_condition.class);
        this.putData('name', drupalSettings.if_then_else.nodes.user_role_condition.name);
        this.putData('classArg', drupalSettings.if_then_else.nodes.user_role_condition.classArg);
        
        //Setting values of retejs condition nodes when editing rule page loads
        this.selected_roles = this.getData('selected_roles');
        this.match = this.getData('match');
        this.input_selection = this.getData('input_selection');
      },
      created() {
        //Triggered when loading retejs editor but before mounted function. See documentaion of Vuejs
        this.radio1_uid = _.uniqueId('radio_');
        this.radio2_uid = _.uniqueId('radio_');

        //Fetching values of fields when editing rule page loads
        for (let role in drupalSettings.if_then_else.nodes.user_role_condition.roles) {
          this.roles.push({
            name: role,
            label: drupalSettings.if_then_else.nodes.user_role_condition.roles[role]
          });
        }
      }
    };
    this.props = { emitter, ikey: key, readonly };
  }

  setValue(val) {
    this.vueContext.value = val;
  }
}