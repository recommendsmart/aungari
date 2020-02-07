class GetTotalQuantityOfProductFromCartControl extends Rete.Control {

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
        <input type="radio" :id="radio3_uid" value="all" v-model="form_selection" @change="formSelectionChanged">
        <label :for="radio3_uid">All Products</label>
      </div>
      
      <div class="radio">
        <input type="radio" :id="radio1_uid" value="list" v-model="form_selection" @change="formSelectionChanged">
        <label :for="radio1_uid">Filter By Product Type</label>
      </div>
      
      <div v-if="form_selection === 'list'">
        <div class="product-type-select">
          <multiselect @wheel.native.stop="wheel" v-model="selected_product_type" :show-labels="false" :options="product_types" 
          placeholder="Product Type" @input="productTypeSelected" label="label" 
          track-by="id" :multiple="true" :taggable="true" @tag="addTag"></multiselect>
        </div>
      </div>

      <div class="radio">
        <input type="radio" :id="radio2_uid" value="other" v-model="form_selection" @change="formSelectionChanged">
        <label :for="radio2_uid">Filter By SKU</label>
      </div>
      
      <div class="other-form-field" v-if="form_selection === 'other'" >
        <div class="product-type-select">
          <multiselect @wheel.native.stop="wheel" v-model="selected_product_sku" :show-labels="false" :options="product_skus" 
          placeholder="Product SKU" @input="productSkuSelected" label="label" 
          track-by="id" :multiple="true" :taggable="true" @tag="addSkuTag"></multiselect>
        </div>
      </div>
      
    </div>`,
      data() {
        return {
          type: drupalSettings.if_then_else.nodes.get_total_quantity_of_product_from_cart_action.type,
          class: drupalSettings.if_then_else.nodes.get_total_quantity_of_product_from_cart_action.class,
          name: drupalSettings.if_then_else.nodes.get_total_quantity_of_product_from_cart_action.name,
          classArg: drupalSettings.if_then_else.nodes.get_total_quantity_of_product_from_cart_action.classArg,
          product_types: [],
          product_skus: [],
          form_selection: 'list',
          selected_product_type: [],
          selected_product_sku: [],
          radio1_uid: '',
          radio2_uid: '',
          radio3_uid: '',
          value: [],
          skuValue: [],
        }
      },
      methods: {
        addTag (newTag) {
          //Multiselect tags
          const tag = {
            id: newTag,
            label: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
          };
          this.product_types.push(tag);
          this.value.push(tag)
        },
        addSkuTag (newTag) {
          //Multiselect tags
          const tag = {
            id: newTag,
            label: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
          };
          this.product_skus.push(tag);
          this.skuValue.push(tag)
        },
        productTypeSelected(value) {
          //Triggered when changing field values
          var selected_product_type = [];
          value.forEach((resource) => {
            selected_product_type.push({id: resource.id, label: resource.label});
          });
          this.putData('selected_product_type',selected_product_type);
          editor.trigger('process');
        },
        productSkuSelected(value) {
          //Triggered when changing field values
          var selected_product_sku = [];
          value.forEach((resource) => {
            selected_product_sku.push({id: resource.id, label: resource.label});
          });
          this.putData('selected_product_sku',selected_product_sku);
          editor.trigger('process');
        },
        formSelectionChanged() {
          this.putData('form_selection', this.form_selection);
          editor.trigger('process');
        },

      },
      mounted() {

        //initialize variable for data
        this.putData('type', drupalSettings.if_then_else.nodes.get_total_quantity_of_product_from_cart_action.type);
        this.putData('class', drupalSettings.if_then_else.nodes.get_total_quantity_of_product_from_cart_action.class);
        this.putData('name', drupalSettings.if_then_else.nodes.get_total_quantity_of_product_from_cart_action.name);
        this.putData('classArg', drupalSettings.if_then_else.nodes.get_total_quantity_of_product_from_cart_action.classArg);

        //Setting values of retejs condition nodes when editing rule page loads
        this.form_selection = this.getData('form_selection');
        var get_selected_options = this.getData('selected_product_type');
        if(typeof get_selected_options != 'undefined'){
          this.value = this.getData('selected_product_type');
        }
        else {
          this.putData('selected_product_type',[]);
        }
        var get_selected_product_sku = this.getData('selected_product_sku');
        if(typeof get_selected_product_sku != 'undefined'){
          this.value = this.getData('selected_product_sku');
        }
        else {
          this.putData('selected_product_sku',[]);
        }
      },
      created() {
        //Triggered when loading retejs editor but before mounted function. See documentaion of Vuejs
        this.radio1_uid = _.uniqueId('radio_');
        this.radio2_uid = _.uniqueId('radio_');
        this.radio3_uid = _.uniqueId('radio_');

        if(drupalSettings.if_then_else.nodes.get_total_quantity_of_product_from_cart_action.product_types){
          this.product_types = drupalSettings.if_then_else.nodes.get_total_quantity_of_product_from_cart_action.product_types;
          this.selected_product_type = this.getData('selected_product_type');
        }
        if(drupalSettings.if_then_else.nodes.get_total_quantity_of_product_from_cart_action.product_skus){
          this.product_skus = drupalSettings.if_then_else.nodes.get_total_quantity_of_product_from_cart_action.product_skus;
          this.selected_product_sku = this.getData('selected_product_sku');
        }
      }
    };
    this.props = { emitter, ikey: key, readonly };
  }

  setValue(val) {
    this.vueContext.value = val;
  }
}
