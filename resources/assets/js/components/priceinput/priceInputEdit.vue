<template>
    <div class="container-fluid">
        <h2>Modify Virtual Machine</h2>
        <div class="panel panel-default">
            <div class="panel-heading">
                <router-link to="/" class="btn btn-warning btn-sm">Back</router-link>
            </div>
            <div class="panel-body">
                <form>
                    <div class="row">
                        <div class="col-xs-12 form-group">
                            <label class="control-label">Region</label>
                            <input type="text" v-model="meter.MeterRegion" class="form-control" disabled="disabled">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 form-group">
                            <label class="control-label">Category</label>
                            <input type="text" v-model="meter.MeterCategory" class="form-control" disabled="disabled">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 form-group">
                            <label class="control-label">Sub Category</label>
                            <input type="text" v-model="meter.MeterSubCategory" class="form-control" disabled="disabled">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 form-group">
                            <label class="control-label">Meter Name</label>
                            <input type="text" v-model="meter.MeterName" class="form-control" disabled="disabled">
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
                    <div class="row">
                        <div class="col-xs-12 form-group">
                            <label class="control-label">Cost</label>
                            <input type="text" v-model="meter.Cost" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 form-group">
                            <button class="btn btn-success" @click="saveForm()">Update</button>
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
        components: { typeahead },
        mounted() {
            let app = this;
            let id = app.$route.params.id;
            app.Id = id;
            axios.get('/api/v1/priceinput/' + id)
                .then(function (resp) {
                    app.meter = resp.data;
                })
                .catch(function () {
                    alert("Could not load your meter")
                });
        },
        data: function () {
            return {
                Id: null,
                meter: {
                    MeterRegion:'',
                    MeterCategory:'',
                    MeterName: '',
                    MeterSubCategory: '',
                    EffectiveDate: '',
                    Cores: '',
                    RAM: '',
                    Cost:''
                },
                errors: [],
            }
        },
        methods: {
            saveForm() {
                //event.preventDefault();
                var app = this;
                var newMeter = app.meter;
                axios.patch('/api/v1/priceinput/' + app.Id, newMeter)
                    .then(function (resp) {
                        app.$router.replace('/');
                    })
                    .catch(function (resp) {
                        console.log(resp);
                        alert("Could not edit your meter");
                    });
            }
        }
    }
</script>
