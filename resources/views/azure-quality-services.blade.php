<!-- View stored in resources/views/greeting.php -->
@extends ('layouts.master_metro')
@section ('head.title')
Azure Quality of Services
@stop

@section('body.content')
    <div class="m-grid__item m-grid__item--fluid  m-grid m-grid--ver-desktop m-grid--desktop m-page__container m-body">
        <div class="m-grid__item m-grid__item--fluid m-wrapper">
            <!-- <h2>Azure Quality of Services</h2> -->
            <div class="m-portlet mt-4">
                <div class="m-portlet__body">
                    <table class='table m-table m-table--head-bg-success table-hover table-bordered'>
                        <col width="50%">
                        <col width="25%">
                        <col width="25%">
                        <thead>
                            <tr>
                                <th>Quality of Services Aspects</th>
                                <th style="text-align:center">{{$quality_of_services['customer']['quality_of_services_aspects']}}</th>
                                <th style="text-align:center">{{$quality_of_services['azure']['quality_of_services_aspects']}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Service Level Agreement</strong></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Uptime guarantees on the infrastructure</td>
                                <td style="text-align:center">{{number_format($quality_of_services['customer']['uptime_guarantees_on_the_infrastructure']*100,2)}}%</td>
                                <td style="text-align:center">{{number_format($quality_of_services['azure']['uptime_guarantees_on_the_infrastructure']*100,2)}}%</td>
                            </tr>
                            <tr>
                                <td>Max. service credits pay out</td>
                                <td style="text-align:center">{{number_format($quality_of_services['customer']['max_service_credits_pay_out']*100,0)}}%</td>
                                <td style="text-align:center">{{number_format($quality_of_services['azure']['max_service_credits_pay_out']*100,0)}}%</td>
                            </tr>
                            <tr>
                                <td><strong>Back-up</strong></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Back-up frequency to recovery vault per 24 hours</td>
                                <td style="text-align:center">{{$quality_of_services['customer']['back_up_frequency_recovery_vault_per_24_hours']}}</td>
                                <td style="text-align:center">{{$quality_of_services['azure']['back_up_frequency_recovery_vault_per_24_hours']}}</td>
                            </tr>
                        <tr>
                                <td>Back-up frequency to disk per 14 hours</td>
                                <td style="text-align:center">{{$quality_of_services['customer']['back_up_frequency_disk_per_14_hours']}}</td>
                                <td style="text-align:center">{{$quality_of_services['azure']['back_up_frequency_disk_per_14_hours']}}</td>
                            </tr>
                            <tr>
                                <td>Retention options</td>
                                <td style="text-align:center">{{$quality_of_services['customer']['retention_options']}}</td>
                                <td style="text-align:center">{{$quality_of_services['azure']['retention_options']}}</td>
                            </tr>
                            <tr>
                                <td>Guaranteed maximum retention period</td>
                                <td style="text-align:center">{{$quality_of_services['customer']['guaranteed_maximum_retention_period']}}</td>
                                <td style="text-align:center">{{$quality_of_services['azure']['guaranteed_maximum_retention_period']}}</td>
                            </tr>
                            <tr>
                                <td>Back-up data encrypted</td>
                                <td style="text-align:center">{{$quality_of_services['customer']['back_up_data_encrypted']}}</td>
                                <td style="text-align:center">{{$quality_of_services['azure']['back_up_data_encrypted']}}</td>
                            </tr>
                            <tr>
                                <td><strong>Disaster Recovery</strong></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Pricing policy for DR</td>
                                <td style="text-align:center">{{$customer_currency_code.' '.number_format($quality_of_services['customer']['pricing_policy_for_dr']*$customer_currency_rate, 0, '.', ',')}}</td>
                                <td style="text-align:center">{{$quality_of_services['azure']['pricing_policy_for_dr']}}</td>
                            </tr>
                            <tr>
                                <td>RPO and RTO guaranteed</td>
                                <td style="text-align:center">{{$quality_of_services['customer']['rpo_and_rto_guaranteed']}}</td>
                                <td style="text-align:center">{{$quality_of_services['azure']['rpo_and_rto_guaranteed']}}</td>
                            </tr>
                            <tr>
                                <td><strong>Compliancy</strong></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Current compliancy certifications</td>
                                <td style="text-align:center">{{$quality_of_services['customer']['current_compliancy_certifications']}}</td>
                                <td style="text-align:center">{{$quality_of_services['azure']['current_compliancy_certifications']}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop