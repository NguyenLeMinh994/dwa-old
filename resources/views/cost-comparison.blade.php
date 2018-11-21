<!-- View stored in resources/views/greeting.php -->
@extends ('layouts.master_metro')
@section ('head.title')
Cost Comparison
@stop

@section('body.content')
    <div class="m-grid__item m-grid__item--fluid  m-grid m-grid--ver-desktop m-grid--desktop m-page__container m-body">
        <div class="m-grid__item m-grid__item--fluid m-wrapper">
            <h2>Cost Comparison</h2>            
            <div class="m-portlet">
                <div class="m-portlet__body">
                    <div class="m-section">
                    <div class="m-section__content">
                    <table class='table m-table m-table--head-bg-success table-hover table-bordered'>
                        <tbody>
                            <thead>
                                <tr>
                                    <th>Main Cost Categories</th>
                                    <th>Currency</th>
                                    <th>Monthly Infrastructure Related Costs</th>
                                    <th>Customer Cost Structure</th>
                                    <th style="color:red">Benchmark</th>
                                    <th>Azure Cost Structure</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Network</td>
                                    <td>{{$currency_code}}</td>
                                    <td>{{number_format(($cost_comparison['network']['monthly_infrastructure_related_costs'] * $currency_rate), 0, '.', ',')}}</td>
                                    <td>{{(number_format($cost_comparison['network']['customer_cost_structure']*100, 0, '.', ',')).'%'}}</td>
                                    <td>{{(number_format($cost_comparison['network']['benchmark_percentage']*100, 0, '.', ',')).'%'}}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Co-location</td>
                                    <td>{{$currency_code}}</td>
                                    <td>{{number_format(($cost_comparison['co_location']['monthly_infrastructure_related_costs'] * $currency_rate), 0, '.', ',')}}</td>
                                    <td>{{(number_format($cost_comparison['co_location']['customer_cost_structure']*100, 0, '.', ',')).'%'}}</td>
                                    <td>{{(number_format($cost_comparison['co_location']['benchmark_percentage']*100, 0, '.', ',')).'%'}}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Total all-in FTE costs per month</td>
                                    <td>{{$currency_code}}</td>
                                    <td>{{number_format(($cost_comparison['total_all_in_FTE_costs_per_month']['monthly_infrastructure_related_costs'] * $currency_rate), 0, '.', ',')}}</td>
                                    <td>{{(number_format($cost_comparison['total_all_in_FTE_costs_per_month']['customer_cost_structure']*100, 0, '.', ',')).'%'}}</td>
                                    <td>{{(number_format($cost_comparison['total_all_in_FTE_costs_per_month']['benchmark_percentage']*100, 0, '.', ',')).'%'}}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Primary Storage</td>
                                    <td>{{$currency_code}}</td>
                                    <td>{{number_format(($cost_comparison['primary_storage']['monthly_infrastructure_related_costs'] * $currency_rate), 0, '.', ',')}}</td>
                                    <td>{{(number_format($cost_comparison['primary_storage']['customer_cost_structure']*100, 0, '.', ',')).'%'}}</td>
                                    <td>{{(number_format($cost_comparison['primary_storage']['benchmark_percentage']*100, 0, '.', ',')).'%'}}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Auxiliary Storage</td>
                                    <td>{{$currency_code}}</td>
                                    <td>{{number_format(($cost_comparison['auxiliary_storage']['monthly_infrastructure_related_costs'] * $currency_rate), 0, '.', ',')}}</td>
                                    <td>{{(number_format($cost_comparison['auxiliary_storage']['customer_cost_structure']*100, 0, '.', ',')).'%'}}</td>
                                    <td>{{(number_format($cost_comparison['auxiliary_storage']['benchmark_percentage']*100, 0, '.', ',')).'%'}}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>General Purpose VM's</td>
                                    <td>{{$currency_code}}</td>
                                    <td>{{number_format(($cost_comparison['general_purpose_VMs']['monthly_infrastructure_related_costs'] * $currency_rate), 0, '.', ',')}}</td>
                                    <td>{{(number_format($cost_comparison['general_purpose_VMs']['customer_cost_structure']*100, 0, '.', ',')).'%'}}</td>
                                    <td>{{(number_format($cost_comparison['general_purpose_VMs']['benchmark_percentage']*100, 0, '.', ',')).'%'}}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Memory Optimised VM's</td>
                                    <td>{{$currency_code}}</td>
                                    <td>{{number_format(($cost_comparison['memory_optimised_VMs']['monthly_infrastructure_related_costs'] * $currency_rate), 0, '.', ',')}}</td>
                                    <td>{{(number_format($cost_comparison['memory_optimised_VMs']['customer_cost_structure']*100, 0, '.', ',')).'%'}}</td>
                                    <td>{{(number_format($cost_comparison['memory_optimised_VMs']['benchmark_percentage']*100, 0, '.', ',')).'%'}}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Compute Optimised VM's</td>
                                    <td>{{$currency_code}}</td>
                                    <td>{{number_format(($cost_comparison['compute_optimised_VMs']['monthly_infrastructure_related_costs'] * $currency_rate), 0, '.', ',')}}</td>
                                    <td>{{(number_format($cost_comparison['compute_optimised_VMs']['customer_cost_structure']*100, 0, '.', ',')).'%'}}</td>
                                    <td>{{(number_format($cost_comparison['compute_optimised_VMs']['benchmark_percentage']*100, 0, '.', ',')).'%'}}</td>
                                    <td></td>
                                    </tr>
                                <tr>
                                    <td>High Performance VM's</td>
                                    <td>{{$currency_code}}</td>
                                    <td>{{number_format(($cost_comparison['high_performance_VMs']['monthly_infrastructure_related_costs'] * $currency_rate), 0, '.', ',')}}</td>
                                    <td>{{(number_format($cost_comparison['high_performance_VMs']['customer_cost_structure']*100, 0, '.', ',')).'%'}}</td>
                                    <td>{{(number_format($cost_comparison['high_performance_VMs']['benchmark_percentage']*100, 0, '.', ',')).'%'}}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>GPU VM's (Needs Calculations)</td>
                                    <td>{{$currency_code}}</td>
                                    <td>{{number_format(($cost_comparison['gpu_VMs']['monthly_infrastructure_related_costs'] * $currency_rate), 0, '.', ',')}}</td>
                                    <td>{{(number_format($cost_comparison['gpu_VMs']['customer_cost_structure']*100, 0, '.', ',')).'%'}}</td>
                                    <td>{{(number_format($cost_comparison['gpu_VMs']['benchmark_percentage']*100, 0, '.', ',')).'%'}}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>WinOS & HypVisor licenses</td>
                                    <td>{{$currency_code}}</td>
                                    <td>{{number_format(($cost_comparison['winos_hypvisor_licenses']['monthly_infrastructure_related_costs'] * $currency_rate), 0, '.', ',')}}</td>
                                    <td>{{(number_format($cost_comparison['winos_hypvisor_licenses']['customer_cost_structure']*100, 0, '.', ',')).'%'}}</td>
                                    <td>{{(number_format($cost_comparison['winos_hypvisor_licenses']['benchmark_percentage']*100, 0, '.', ',')).'%'}}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Linux licenses</td>
                                    <td>{{$currency_code}}</td>
                                    <td>{{number_format(($cost_comparison['linux_licenses']['monthly_infrastructure_related_costs'] * $currency_rate), 0, '.', ',')}}</td>
                                    <td>{{(number_format($cost_comparison['linux_licenses']['customer_cost_structure']*100, 0, '.', ',')).'%'}}</td>
                                    <td>{{(number_format($cost_comparison['linux_licenses']['benchmark_percentage']*100, 0, '.', ',')).'%'}}</td>
                                    <td></td>
                                </tr>
                                <tr style="font-weight:bold">
                                    <td>Total</td>
                                    <td>{{$currency_code}}</td>
                                    <td>{{number_format(($cost_comparison['total_cost']['monthly_infrastructure_related_costs'] * $currency_rate), 0, '.', ',')}}</td>
                                    <td>{{(number_format($cost_comparison['total_cost']['customer_cost_structure']*100, 0, '.', ',')).'%'}}</td>
                                    <td>{{(number_format($cost_comparison['total_cost']['benchmark_percentage']*100, 0, '.', ',')).'%'}}</td>
                                    <td></td>
                                </tr>                                                       
                            </tbody>
                        </tbody>
                    </table>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop