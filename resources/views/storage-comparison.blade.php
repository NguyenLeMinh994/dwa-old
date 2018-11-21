<!-- View stored in resources/views/greeting.php -->
@extends ('layouts.master_metro')
@section ('head.title')
Storage Comparison
@stop

@section('body.content')
    <div class="m-grid__item m-grid__item--fluid  m-grid m-grid--ver-desktop m-grid--desktop m-page__container m-body">
        <div class="m-grid__item m-grid__item--fluid m-wrapper">
            <h2>Storage Comparison</h2>
            <div class="row">
                <div class="col-md-3">
                    <div class="m-portlet m-portlet--responsive-mobile m-portlet--warning m-portlet--head-solid-bg m-portlet--bordered">
                        <div class="m-portlet__head">
                            <div class="m-portlet__head-caption">
                                <div class="m-portlet__head-title">
                                    <h5 class="m-portlet__head-text">GB RAM used for General Purpose VM</h5>
                                </div>
                            </div>
                        </div>
                        <div class="m-portlet__body">
                            <div class="chart_content" style="text-align:center" id="chartdiv_comparison_linux">
                                <?php 
                                    // ID33 * (1-Percent of the total GB/RAM footprint used)
                                    $gb_ram_user_for_GP = 4596 * (1 - 0.41393);
                                ?>
                                <h2>{{number_format(round($gb_ram_user_for_GP), 2, '.', ',')}}</h2>
                                <span style="color:red">QP ID33</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="m-portlet m-portlet--responsive-mobile m-portlet--warning m-portlet--head-solid-bg m-portlet--bordered">
                        <div class="m-portlet__head">
                            <div class="m-portlet__head-caption">
                                <div class="m-portlet__head-title">
                                    <h5 class="m-portlet__head-text">GB RAM used for Non-Production VM</h5>
                                </div>
                            </div>
                        </div>
                        <div class="m-portlet__body">
                            <div class="chart_content" style="text-align:center" id="chartdiv_comparison_linux">
                                <h2>
                                    <!-- QP 35 -->
                                    {{number_format(510, 2, '.', ',')}}
                                </h2>
                                <span style="color:red">QP ID35</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="m-portlet m-portlet--responsive-mobile m-portlet--warning m-portlet--head-solid-bg m-portlet--bordered">
                        <div class="m-portlet__head">
                            <div class="m-portlet__head-caption">
                                <div class="m-portlet__head-title">
                                    <h5 class="m-portlet__head-text"></h5>
                                </div>
                            </div>
                        </div>
                        <div class="m-portlet__body">
                            <div class="chart_content" style="text-align:center" id="chartdiv_comparison_linux">
                                <h2></h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="m-portlet m-portlet--responsive-mobile m-portlet--warning m-portlet--head-solid-bg m-portlet--bordered">
                        <div class="m-portlet__head">
                            <div class="m-portlet__head-caption">
                                <div class="m-portlet__head-title">
                                    <h5 class="m-portlet__head-text"></h5>
                                </div>
                            </div>
                        </div>
                        <div class="m-portlet__body">
                            <div class="chart_content" style="text-align:center" id="chartdiv_comparison_linux">
                                <h2></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="m-portlet">
                <div class="m-portlet__head">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <h5 class="m-portlet__head-text">Price per GBRAM for Primary Storage</h5>
                        </div>
                    </div>
                </div>
                <div class="m-portlet__body">
                    <table class='datatable table table-hover table-bordered'>
                        <col width="50%">
                        <col width="25%">
                        <col width="25%">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Customer Cost</th>
                                <th>Azure Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Price per GBRAM in use for GP Win</td>
                                <td>
                                <?php
                                /*
                                SUM Of [
                                    Network
                                    Co-Location
                                    Total all-in FTE costs per month
                                    WinOS & HypVisor licenses
                                    Linux License
                                    General Purpose VM's (Needs Calulations) 
                                ] * General Purpose Weighted VM Allocation 
                                */
                                ?>    
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Price per GBRAM in use for GP Linux</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Price per GBRAM in use for MO Win</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Price per GBRAM in use for MO Linux</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Price per GBRAM in use for CO Win</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Price per GBRAM in use for CO Linux</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Price per GBRAM in use for HP Win</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Price per GBRAM in use for HP Linux</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Price per GBRAM in use for GPU Win</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Price per GBRAM in use for GPU Linux</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="m-portlet">
                <div class="m-portlet__head">
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <h5 class="m-portlet__head-text">Storage cost factors comparison</h5>
                        </div>
                    </div>
                </div>
                <div class="m-portlet__body">
                    <table class='datatable table table-hover table-bordered'>
                        <col width="50%">
                        <col width="25%">
                        <col width="25%">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Customer Cost</th>
                                <th>Azure Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Price per GBRAM for Primary Storage</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Price per GBRAM for Auxiliary Storage</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop