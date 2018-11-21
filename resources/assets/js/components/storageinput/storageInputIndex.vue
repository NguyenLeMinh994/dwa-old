<template>
    <div class="container-fluid">
        <h2>Storage Input</h2>
        <div class="panel panel-default panel-table">
            <div class="panel-heading">
                <router-link :to="{name: 'createVM'}" class="btn btn-success btn-sm">Add New</router-link>
            </div>
            <div class="panel-body">
                <table class='datatable table table-hover table-bordered'>
                    <tbody>
                        <tr>
                            <th>Id</th>
                            <th>Region</th>
                            <th>Sub Category</th>
                            <th>Meter Name</th>
                            <!-- <th>Unit</th> -->
                            <th>Core</th>
                            <th>GB RAM</th>
                            <th>Ratio</th>
                            <th>Currency</th>
                            <th>Cost</th>
                            <th>Effective Date</th>
                            <th>Included Quantity</th>
                            <th>Updated At</th>
                            <th>Action</th>
                        </tr>
                        <tr v-for="(meter, index) in meters"  v-bind:key="index">
                            <td>{{ meter.Id }}</td>
                            <td>{{ meter.MeterRegion }}</td>
                            <td>{{ meter.MeterSubCategory }}</td>
                            <td>{{ meter.MeterName }}</td>
                            <!-- <td>{{ meter.Unit }}</td> -->
                            <td>{{ meter.Cores }}</td>
                            <td>{{ meter.RAM }}</td>
                            <td>{{ meter.Ratio }}</td>
                            <td>{{ meter.Currency }}</td>
                            <td>{{ meter.Cost }}</td>
                            <td>{{ meter.MeterRates }}</td>
                            <td>{{ meter.EffectiveDate }}</td>
                            <!-- <td>{{ meter.IncludedQuantity }}</td> -->
                            <td>{{ meter.updated_at }}</td>
                            <td>
                                <router-link :to="{name: 'editVM', params: {id: meter.Id}}" class="btn btn-xs btn-info">&nbsp;&nbsp;Edit&nbsp;&nbsp;</router-link>
                                <a href="#" class="btn btn-xs btn-danger" v-on:click="deleteEntry(meter.Id, index)">Delete</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="panel-footer"></div>
        </div>
    </div>
</template>
<script>
    export default {
        data: function () {
            return {
                meters: []
            }
        },
        mounted() {
            var app = this;
            axios.get('/api/v1/priceinput')
                .then(function (resp) {
                    app.meters = resp.data;
                })
                .catch(function (resp) {
                    console.log(resp);
                    alert("Could not load VM list");
                });
        },
        methods: {
            deleteEntry(id, index) {
                if (confirm("Do you really want to delete it?")) {
                    var app = this;
                    axios.delete('/api/v1/priceinput/' + id)
                        .then(function (resp) {
                            app.meters.splice(index, 1);
                        })
                        .catch(function (resp) {
                            alert("Could not this VM");
                        });
                }
            }
        }
    }
</script>
