<template>
    <div class="container-fluid">
        <h3>New Price Record</h3>
        <div class="panel panel-default">
            <div class="panel-heading">
                <router-link to="/" class="btn btn-warning btn-sm">Back</router-link>
            </div>
            <div class="panel-body">
                <form id="create-from">
                    <div class="row">
                        <div class="col-xs-12 form-group">
                            <label class="control-label">Region</label>
                            <!-- <input type="text" v-model="meter.MeterRegion" class="form-control"> -->
                            <typeahead :url="regionAPI" :initialize="meter.RegionRender" @input="onSuggestMeterRegion" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 form-group">
                            <label class="control-label">Category</label>
                            <!-- <input type="text" v-model="meter.MeterCategory" id="tag" class="form-control"> -->
                            <typeahead :url="categoriesAPI" :initialize="meter.CategoryRender" @input="onSuggestMeterCategory" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 form-group">
                            <label class="control-label">Sub Category</label>
                            <!-- <input type="text" v-model="meter.MeterSubCategory" class="form-control"> -->
                            <typeahead :url="subCategoriesAPI" :initialize="meter.SubCategoryRender" @input="onSuggestMeterSubCategory" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 form-group">
                            <label class="control-label">Meter Name</label>
                            <!-- <input type="text" v-model="meter.MeterName" class="form-control"> -->
                            <typeahead :url="metersAPI" :initialize="meter.MetersRender" @input="onSuggestMeters" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 form-group">
                            <label class="control-label">Meter Type</label>
                            <input type="text" v-model="meter.MeterTypes" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 form-group">
                            <label class="control-label">Meter Function</label>
                            <input type="text" v-model="meter.MeterFunction" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 form-group">
                            <label class="control-label">Cores</label>
                            <input type="text" v-model="meter.Cores" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 form-group">
                            <label class="control-label">GB RAM</label>
                            <input type="text" v-model="meter.RAM" class="form-control">
                        </div>
                    </div>
                    <!--
                    <div class="row">
                        <div class="col-xs-12 form-group">
                            <label class="control-label">Effective Date</label>
                            <input type="text" v-model="meter.EffectiveDate" class="form-control">
                        </div>
                    </div> -->
                    <div class="row">
                        <div class="col-xs-12 form-group">
                            <label class="control-label">Cost</label>
                            <input type="text" v-model="meter.MeterRates" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 form-group">
                            <button class="btn btn-success" @click="saveForm()">Add New</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
<script>
    import Vue from 'vue'
    import typeahead from '../../components/typeahead/Typeahead.vue';
    export default {
        components: { typeahead},
        data: function () {
            return {
                meter: {
                    MeterRegion:'',
                    MeterCategory:'',
                    MeterId:'',
                    MeterName: '',
                    MeterTypes: '',
                    MeterFunction: '',
                    MeterSubCategory: '',
                    MeterRates: '',
                    Cores: '',
                    RAM: '',
                    Currency: 'USD',
                    Cost:''
                },
                errors: [],
                regionAPI: '/suggest-regions',
                categoriesAPI: '/suggest-categories',
                subCategoriesAPI: '/suggest-subcategories',
                metersAPI: '/suggest-meters',
            }
        },
        methods: {
            saveForm() {
                //event.preventDefault();
                var app = this;
                var newMeter = app.meter;
                axios.post('/api/v1/priceinput', newMeter)
                .then(response => {
                    app.$router.push({path: '/'}); // back to list
                })
                .catch(error => {
                    //console.log(resp);
                    this.errors = [];
                    if (error.response.data.errors.name) {
                        this.errors.push(error.response.data.errors.name[0]);
                    }

                    if (error.response.data.errors.description) {
                        this.errors.push(error.response.data.errors.description[0]);
                    }
                    alert("Could not add new virtual machine");
                });
            },
            onSuggestMeterRegion(e) {
                const regionObject = e.target.value;
                Vue.set(this.meter, 'RegionRender', regionObject);
                Vue.set(this.meter, 'MeterRegion', regionObject.text);

                //categoriesAPI = '/suggest-categories?region='+regionObject.text;
            },
            onSuggestMeterCategory(e) {
                const categoryObject = e.target.value
                Vue.set(this.meter, 'CategoryRender', categoryObject);
                Vue.set(this.meter, 'MeterCategory', categoryObject.text);
            },
            onSuggestMeterSubCategory(e) {
                const subCategoryObject = e.target.value
                Vue.set(this.meter, 'SubCategoryRender', subCategoryObject);
                Vue.set(this.meter, 'MeterSubCategory', subCategoryObject.text);
            },
            onSuggestMeters(e) {
                const metersObject = e.target.value
                let meterRates = metersObject.MeterRates.split(";")[0];
                let cost = meterRates.split(":")[1];

                Vue.set(this.meter, 'MetersRender', metersObject);
                Vue.set(this.meter, 'MeterName', metersObject.text);
                Vue.set(this.meter, 'MeterId', metersObject.id);
                Vue.set(this.meter, 'MeterRates', cost);
            },
        }
    }
</script>
