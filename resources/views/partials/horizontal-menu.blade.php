<?php
    $active_menu = array();
    $active_menu['customers'] = "";
    $active_menu['dashboard'] = "";
    $active_menu['current'] = "";
    $active_menu['qos'] = "";
    $active_menu['benefit'] = "";
    $active_menu['business_case'] = "";
    $active_menu['output'] = "";

    $active_menu['database'] = "";
    $active_menu['comparison'] = "";
    $active_menu['admin_tools'] = "";

    $current_uri = request()->route()->uri();
    switch ($current_uri) {

        //customer menu
        case 'survey-results':
            $active_menu['customers'] = "m-menu__item--active m-menu__item--active-tab";
            break;
        
        //current
        case 'current':
            $active_menu['current'] = "m-menu__item--active m-menu__item--active-tab";
            break;
        case 'current-cost-structure':
            $active_menu['current'] = "m-menu__item--active m-menu__item--active-tab";
            break;

        //Azure menu
        case 'dashboard':
            $active_menu['dashboard'] = "m-menu__item--active m-menu__item--active-tab";
            break;

        //Benefit
        case 'azure-benefits':
            $active_menu['benefit'] = "m-menu__item--active m-menu__item--active-tab";
            break;

        //Qos menu
        case 'azure-quality-services':
            $active_menu['qos'] = "m-menu__item--active m-menu__item--active-tab";
            break;
        
        //Output menu
        case 'export':
            $active_menu['output'] = "m-menu__item--active m-menu__item--active-tab";
            break;

        case 'azure-cost-comparison':
            $active_menu['dashboard'] = "m-menu__item--active m-menu__item--active-tab";
            break;
        
        
        //business case menu
        case 'business-case':
            $active_menu['business_case'] = "m-menu__item--active m-menu__item--active-tab";
            break;
        case 'scenario1-calculation':
            $active_menu['admin_tools'] = "m-menu__item--active m-menu__item--active-tab";
            break;
        case 'scenario2-calculation':
            $active_menu['admin_tools'] = "m-menu__item--active m-menu__item--active-tab";
            break;
        case 'scenario3-calculation':
            $active_menu['admin_tools'] = "m-menu__item--active m-menu__item--active-tab";
            break;

        //database menu
        case 'rates':
            $active_menu['admin_tools'] = "m-menu__item--active m-menu__item--active-tab";
            break;
        case 'price-categories':
            $active_menu['admin_tools'] = "m-menu__item--active m-menu__item--active-tab";
            break;
        case 'storage-categories':
            $active_menu['admin_tools'] = "m-menu__item--active m-menu__item--active-tab";
            break;
        case 'asr-categories':
            $active_menu['admin_tools'] = "m-menu__item--active m-menu__item--active-tab";
            break;
        case 'valuta':
            $active_menu['admin_tools'] = "m-menu__item--active m-menu__item--active-tab";
            break;
        

        //comparison menu
        case 'vm-comparison':
            $active_menu['admin_tools'] = "m-menu__item--active m-menu__item--active-tab";
            break;
        case 'storage-comparison':
            $active_menu['admin_tools'] = "m-menu__item--active m-menu__item--active-tab";
            break;
        case 'cost-comparison':
            $active_menu['admin_tools'] = "m-menu__item--active m-menu__item--active-tab";
            break;
        
        //admin_tools menu
        case 'variable-comparison':
            $active_menu['admin_tools'] = "m-menu__item--active m-menu__item--active-tab";
            break;
        case 'reserved-instances':
            $active_menu['admin_tools'] = "m-menu__item--active m-menu__item--active-tab";
            break;  
        case 'variable-stragetic':
            $active_menu['admin_tools'] = "m-menu__item--active m-menu__item--active-tab";
            break;
        case 'cpu-benchmarks':
            $active_menu['admin_tools'] = "m-menu__item--active m-menu__item--active-tab";
            break;
        
        default:
            $active_menu['customers'] = "m-menu__item--active m-menu__item--active-tab";
            break;
    }
?>
<div class="m-stack__item m-stack__item--fluid m-header-menu-wrapper">
    <button class="m-aside-header-menu-mobile-close  m-aside-header-menu-mobile-close--skin-light " id="m_aside_header_menu_mobile_close_btn">
        <i class="la la-close"></i>
    </button>
    <div id="m_header_menu" class="m-header-menu m-aside-header-menu-mobile m-aside-header-menu-mobile--offcanvas  m-header-menu--skin-dark m-header-menu--submenu-skin-light m-aside-header-menu-mobile--skin-light m-aside-header-menu-mobile--submenu-skin-light">
        <ul class="m-menu__nav m-menu__nav--submenu-arrow ">
            <li class="m-menu__item  m-menu__item--submenu {{$active_menu['customers']}} m-menu__item--tabs"  m-menu-submenu-toggle="tab" aria-haspopup="true">
                <a href="/survey-results" class="m-menu__link">
                    <span class="m-menu__link-text">{{ trans('menu.validate') }}</span>
                    <i class="m-menu__hor-arrow la la-angle-down"></i>
                    <i class="m-menu__ver-arrow la la-angle-right"></i>
                </a>
            </li>
            
            <li class="m-menu__item  m-menu__item--submenu {{$active_menu['current']}} m-menu__item--tabs"  m-menu-submenu-toggle="tab" aria-haspopup="true">
                <a href="/current-cost-structure" class="m-menu__link">
                    <span class="m-menu__link-text">{{ trans('menu.current') }}</span>
                    <i class="m-menu__hor-arrow la la-angle-down"></i>
                    <i class="m-menu__ver-arrow la la-angle-right"></i>
                </a>
            </li>

            <li class="m-menu__item m-menu__item--submenu {{$active_menu['dashboard']}} m-menu__item--tabs" m-menu-submenu-toggle="tab" aria-haspopup="true">
                <a href="#" class="m-menu__link">
                    <span class="m-menu__link-text">{{ trans('menu.azure') }}</span>
                    <i class="m-menu__hor-arrow la la-angle-down"></i>
                    <i class="m-menu__ver-arrow la la-angle-right"></i>
                </a>
            </li>

            <li class="m-menu__item  m-menu__item--submenu {{$active_menu['benefit']}} m-menu__item--tabs"  m-menu-submenu-toggle="tab" aria-haspopup="true">
                <a  href="#" class="m-menu__link">
                    <span class="m-menu__link-text">{{ trans('menu.benefit') }}</span>
                    <i class="m-menu__hor-arrow la la-angle-down"></i>
                    <i class="m-menu__ver-arrow la la-angle-right"></i>
                </a>
            </li>

            <li class="m-menu__item  m-menu__item--submenu {{$active_menu['qos']}} m-menu__item--tabs"  m-menu-submenu-toggle="tab" aria-haspopup="true">
                <a  href="/azure-quality-services" class="m-menu__link">
                    <span class="m-menu__link-text" style="text-transform: none;">{{ trans('menu.qos') }}</span>
                    <i class="m-menu__hor-arrow la la-angle-down"></i>
                    <i class="m-menu__ver-arrow la la-angle-right"></i>
                </a>
            </li>    

            <li class="m-menu__item  m-menu__item--submenu {{$active_menu['business_case']}} m-menu__item--tabs" m-menu-submenu-toggle="tab" aria-haspopup="true">
                <a  href="#" class="m-menu__link">
                    <span class="m-menu__link-text">{{ trans('menu.scenarios') }}</span>
                    <i class="m-menu__hor-arrow la la-angle-down"></i>
                    <i class="m-menu__ver-arrow la la-angle-right"></i>
                </a>
            </li>

            <li class="m-menu__item  m-menu__item--submenu {{$active_menu['output']}} m-menu__item--tabs"  m-menu-submenu-toggle="tab" aria-haspopup="true">
                <a href="/export" class="m-menu__link">
                    <span class="m-menu__link-text">{{ trans('menu.output') }}</span>
                    <i class="m-menu__hor-arrow la la-angle-down"></i>
                    <i class="m-menu__ver-arrow la la-angle-right"></i>
                </a>
            </li>
            
            
            <script>
                function openSubMenu(menuID){
                    let subMenus = ["scenarioSub", "comparisonSub", "RISub", "StaticSub"];
                    if($("#"+menuID).hasClass("m-menu__item--hover") == false)
                        $("#"+menuID).addClass("m-menu__item--open-dropdown m-menu__item--hover");
                    else
                        $("#"+menuID).removeClass("m-menu__item--open-dropdown m-menu__item--hover");

                    //close other sub menu
                    $.each(subMenus, function (index, value) {
                        if(value != menuID){
                            $("#"+value).removeClass("m-menu__item--open-dropdown m-menu__item--hover");
                        }
                    });
                }
            </script>
            <li class="m-menu__item {{$active_menu['admin_tools']}} m-menu__item--submenu m-menu__item--tabs"  m-menu-submenu-toggle="tab" aria-haspopup="true">
                <a href="/cost-comparison" class="m-menu__link m-menu__toggle">
                    <span class="m-menu__link-text">{{ trans('menu.admin') }}</span>
                    <i class="m-menu__hor-arrow la la-angle-down"></i>
                    <i class="m-menu__ver-arrow la la-angle-right"></i>
                </a>
                <div class="m-menu__submenu m-menu__submenu--classic m-menu__submenu--left m-menu__submenu--tabs">
                    <span class="m-menu__arrow m-menu__arrow--adjust"></span>
                    <ul class="m-menu__subnav">
                        <li id="comparisonSub" onclick="openSubMenu('comparisonSub')" class="m-menu__item m-menu__item--submenu m-menu__item--rel m-menu__item--submenu-tabs" m-menu-link-redirect="1" aria-haspopup="true"  m-menu-submenu-toggle="click">
                            <a href="javascript:;" class="m-menu__link m-menu__toggle">
                                <i class="m-menu__link-icon flaticon-settings-1"></i>
                                <span class="m-menu__link-text">
                                    Comparison
                                </span>
                                <i class="m-menu__hor-arrow la la-angle-down"></i>
                                <i class="m-menu__ver-arrow la la-angle-right"></i>
                            </a>
                            <div class="m-menu__submenu m-menu__submenu--classic m-menu__submenu--left">
                                <span class="m-menu__arrow m-menu__arrow--adjust" style="left: 71.5px;"></span>
                                <ul class="m-menu__subnav">
                                    <li class="m-menu__item " m-menu-link-redirect="1" aria-haspopup="true">
                                        <a  href="/vm-comparison" class="m-menu__link ">
                                            <i class="m-menu__link-icon flaticon-graphic-2"></i>
                                            <span class="m-menu__link-text">
                                                VM Comparison
                                            </span>
                                        </a>
                                    </li>
                                    <li class="m-menu__item " m-menu-link-redirect="1" aria-haspopup="true">
                                        <a  href="/cost-comparison" class="m-menu__link ">
                                            <i class="m-menu__link-icon flaticon-graphic-2"></i>
                                            <span class="m-menu__link-text">
                                                Cost Comparison
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li id="StaticSub" onclick="openSubMenu('StaticSub')" class="m-menu__item m-menu__item--submenu m-menu__item--rel m-menu__item--submenu-tabs" m-menu-link-redirect="1" aria-haspopup="true"  m-menu-submenu-toggle="click">
                            <a href="javascript:;" class="m-menu__link m-menu__toggle">
                                <i class="m-menu__link-icon fa fa-database"></i>
                                <span class="m-menu__link-text">
                                    Azure Categories
                                </span>
                                <i class="m-menu__hor-arrow la la-angle-down"></i>
                                <i class="m-menu__ver-arrow la la-angle-right"></i>
                            </a>
                            <div class="m-menu__submenu m-menu__submenu--classic m-menu__submenu--left">
                                <span class="m-menu__arrow m-menu__arrow--adjust" style="left: 71.5px;"></span>
                                <ul class="m-menu__subnav">
                                    <li class="m-menu__item " m-menu-link-redirect="1" aria-haspopup="true">
                                        <a href="/rates" class="m-menu__link ">
                                            <i class="m-menu__link-icon flaticon-graphic-2"></i>
                                            <span class="m-menu__link-text">
                                                Azure RateCard Static
                                            </span>
                                        </a>
                                    </li>
                                    <li class="m-menu__item " m-menu-link-redirect="1" aria-haspopup="true">
                                        <a href="/price-categories" class="m-menu__link ">
                                            <i class="m-menu__link-icon flaticon-graphic-2"></i>
                                            <span class="m-menu__link-text">
                                                Virtual Machine Categories
                                            </span>
                                        </a>
                                    </li>
                                    <li class="m-menu__item " m-menu-link-redirect="1" aria-haspopup="true">
                                        <a href="/storage-categories" class="m-menu__link ">
                                            <i class="m-menu__link-icon flaticon-graphic-2"></i>
                                            <span class="m-menu__link-text">
                                                Storage Categories
                                            </span>
                                        </a>
                                    </li>
                                    <li class="m-menu__item " m-menu-link-redirect="1" aria-haspopup="true">
                                        <a href="/asr-categories" class="m-menu__link ">
                                            <i class="m-menu__link-icon flaticon-graphic-2"></i>
                                            <span class="m-menu__link-text">
                                                ASR Categories
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="m-menu__item " m-menu-link-redirect="1" aria-haspopup="true">
                            <a href="/valuta" class="m-menu__link ">
                                <i class="m-menu__link-icon fa fa-dollar-sign"></i>
                                <span class="m-menu__link-text">
                                    Currencies
                                </span>
                            </a>
                        </li>
                        <li class="m-menu__item " m-menu-link-redirect="1" aria-haspopup="true">
                            <a href="/cpu-benchmarks" class="m-menu__link ">
                                <i class="m-menu__link-icon flaticon-graphic-2"></i>
                                <span class="m-menu__link-text">
                                    CPU Benchmarks
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <!--    
            <li class="m-menu__item  m-menu__item--submenu m-menu__item--tabs"  m-menu-submenu-toggle="tab" aria-haspopup="true">
                <a  href="#" class="m-menu__link m-menu__toggle">
                    <span class="m-menu__link-text">Back to Reseller Portal</span>
                    <i class="m-menu__hor-arrow la la-angle-down"></i>
                    <i class="m-menu__ver-arrow la la-angle-right"></i>
                </a>
                <div class="m-menu__submenu m-menu__submenu--classic m-menu__submenu--left m-menu__submenu--tabs">
                </div>
            </li> -->
        </ul>
    </div>
</div>