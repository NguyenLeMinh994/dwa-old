<!-- begin::Header -->
<header id="m_header" class="m-grid__item m-header " m-minimize="minimize" m-minimize-mobile="minimize" m-minimize-offset="200" m-minimize-mobile-offset="200" >
    <div class="m-header__top">
        <div class="m-container m-container--fluid m-container--full-height m-page__container">
            <div class="m-stack m-stack--ver m-stack--desktop">
                <!-- begin::Brand -->
                <div class="m-stack__item m-brand m-stack__item--left">
                    <div class="m-stack m-stack--ver m-stack--general m-stack--inline">
                        <div class="m-stack__item m-stack__item--middle m-brand__logo">
                            <a href="#" class="m-brand__logo-wrapper">
                                <img alt="" src="assets/app/media/img/logo/cloudlab_transparent_105x80.png" class="m-brand__logo-desktop"/>
                                <img alt="" src="assets/app/media/img/logo/cloudlab_transparent_mobile.png" class="m-brand__logo-mobile"/>
                            </a>
                        </div>
                        <div class="m-stack__item m-stack__item--middle m-brand__tools">
                            <?php
                                $customer_setup_config = session('customer_setup_config');
                                $current_region = $customer_setup_config['azure_locale'];
                                $customer_currency = $customer_setup_config['currency']['currency_code'];
                            ?>
                            <div id="m_header_menu" class="m-header-menu m-aside-header-menu-mobile m-aside-header-menu-mobile--offcanvas m-header-menu--skin-light m-header-menu--submenu-skin-light">
                                <ul class="m-menu__nav  m-menu__nav--submenu-arrow ">
                                    <li class="m-menu__item m-menu__item--submenu m-menu__item--rel m-menu__item--open-dropdown" m-menu-submenu-toggle="click" aria-haspopup="true">
                                        <a href="javascript: void(0)" class="m-menu__link m-menu__toggle">
                                            <i class="m-menu__link-icon flaticon-placeholder"></i>
                                            <span class="m-menu__link-text">Azure Region: {{$current_region}}</span>
                                            <i class="m-menu__hor-arrow la la-angle-down"></i>
                                            <i class="m-menu__ver-arrow la la-angle-right"></i>
                                        </a>
                                        <div class="m-menu__submenu m-menu__submenu--fixed m-menu__submenu--left" style="width:1000px">
                                            <?php $region_list = \Cache::get('regions_meter'); ?>
                                            <span class="m-menu__arrow m-menu__arrow--adjust" style="left: 71.5px;"></span>
                                            <div class="m-menu__subnav">
                                                <ul class="m-menu__content">
                                                    @foreach($region_list as $group_key => $group_list)
                                                    <li class="m-menu__item">
                                                        <h3 class="m-menu__heading m-menu__toggle">
                                                            <span class="m-menu__link-text">{{$group_key}}</span>
                                                            <i class="m-menu__ver-arrow la la-angle-right"></i>
                                                        </h3>
                                                        <ul class="m-menu__inner">
                                                            @foreach($group_list as $region_key => $region_item)
                                                            <li class="m-menu__item " m-menu-link-redirect="1" aria-haspopup="true">
                                                                <a href="javascript:confirmSwitch('Region', '{{$current_region}}', '{{$region_item->meter}}');" class="m-menu__link ">
                                                                    <i class="m-menu__link-icon flaticon-placeholder-2"></i>
                                                                    <!-- <span class="m-menu__link-text" style="text-align:left">{{($region_item->region_name != null) ? $region_item->region_name : $region_item->meter}}</span> -->
                                                                    <span class="m-menu__link-text" style="text-align:left">{{$region_item->meter}}</span>
                                                                </a>
                                                            </li>
                                                            @endforeach
                                                        </ul>
                                                    </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <!-- begin::Responsive Header Menu Toggler-->
                            <a id="m_aside_header_menu_mobile_toggle" href="javascript:;" class="m-brand__icon m-brand__toggler m--visible-tablet-and-mobile-inline-block">
                                <span></span>
                            </a>
                            <!-- end::Responsive Header Menu Toggler-->
                                
                            <!-- begin::Topbar Toggler-->
                            <a id="m_aside_header_topbar_mobile_toggle" href="javascript:;" class="m-brand__icon m--visible-tablet-and-mobile-inline-block">
                                <i class="flaticon-more"></i>
                            </a>
                            <!--end::Topbar Toggler-->								
                        </div>
                    </div>
                </div>
                <!-- end::Brand -->		
                
                <!-- begin::Topbar -->
                <?php 
                    $current_language = '';
                    if(Lang::locale() === 'en'){
                        $current_language = 'English';
                        $current_flag = 'gb.png';
                    }
                    if(Lang::locale() === 'de'){
                        $current_language = 'Deutsch';
                        $current_flag = 'de.png';
                    }
                    if(Lang::locale() === 'fr'){
                        $current_language = 'Français';
                        $current_flag = 'fr.png';
                    }
                ?>
                <script>
                    function switchLanguage(lang){
                        $("#locale").val(lang);
                        $("#submitLang").submit();
                    }
                    
                    function confirmSwitch(section, currentValue, changeValue) {
                        if (currentValue!=changeValue){
                            switch(section){
                                case "Region":
                                    $('#switchForm').attr('action', "/region");
                                    $('#contentChange').attr('name', "region");
                                    $('#contentChange').val(changeValue);
                                    break;
                                case "Language":
                                    $('#switchForm').attr('action', "/lang");
                                    $('#contentChange').attr('name', "locale");
                                    $('#contentChange').val(changeValue);
                                    break;
                                case "Currency":
                                    $('#switchForm').attr('action', "/currency");
                                    $('#contentChange').attr('name', "currency");
                                    $('#contentChange').val(changeValue);
                                    break;
                            }
                            document.getElementById('switchContent').innerHTML = "<p>You are changing the "+section+" display from <strong>"+currentValue+"</strong> to <strong>"+changeValue+"</strong>.</p><p>Please select only one "+section.toLowerCase()+" for the entire case so that the chart data can be exported in the Output section.</p>";
                            $("#switch_notify_modal").modal('show');
                        }
                    }
                </script>
                <form action="/lang" id="submitLang" class="form-lang" method="post">
                    <input type="hidden" value="" id="locale" name="locale"/>
                    {{ csrf_field() }}
                </form>
                
                <div class="m-stack__item m-stack__item--right m-header-head" id="m_header_nav">
                    <div id="m_header_topbar" class="m-topbar  m-stack m-stack--ver m-stack--general">
                        <div class="m-stack__item m-topbar__nav-wrapper">
                            <ul class="m-topbar__nav m-nav m-nav--inline">
                                <li class="m-nav__item m-topbar__languages m-dropdown m-dropdown--small m-dropdown--arrow m-dropdown--align-right m-dropdown--mobile-full-width" m-dropdown-toggle="click" aria-expanded="true">
                                    <a href="#" class="m-nav__link m-dropdown__toggle">
                                        <span style="width:15px" class="m-nav__link-icon">
                                            @if ($customer_currency == 'EUR')
                                                <i class="fa fa-euro-sign"></i>
                                            @endif
                                            @if ($customer_currency == 'USD')
                                                <i class="fa fa-dollar-sign"></i>
                                            @endif
                                            @if ($customer_currency == 'GBP')
                                                <i class="fa fa-pound-sign"></i>
                                            @endif
                                        </span>
                                        <span style="width:15px" class="m-nav__link-title m-topbar__language-text m-nav__link-text">{{$customer_currency}}</span>
                                    </a>
                                    <div class="m-dropdown__wrapper" style="z-index: 101;">
                                        <span style="left: auto; right: 5px;" class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust" ></span>
                                        <div class="m-dropdown__inner">
                                            <div class="m-dropdown__header m--align-center">
                                                <span class="m-dropdown__header-subtitle">Select your currency</span>
                                            </div>
                                            <div class="m-dropdown__body">
                                                <div class="m-dropdown__content">
                                                    <ul class="m-nav m-nav--skin-light">
                                                        <li class="m-nav__item m-nav__item--active">
                                                            <a href="javascript:confirmSwitch('Currency', '{{$customer_currency}}', 'USD');" class="m-nav__link m-nav__link--active">
                                                                <span class="m-nav__link-icon">
                                                                    <i class="fa fa-dollar-sign"></i>
                                                                </span>
                                                                <span class="m-nav__link-title m-topbar__language-text m-nav__link-text">USD</span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item m-nav__item--active">
                                                            <a href="javascript:confirmSwitch('Currency', '{{$customer_currency}}', 'EUR')" class="m-nav__link m-nav__link--active">
                                                                <span class="m-nav__link-icon">
                                                                    <i class="fa fa-euro-sign"></i>
                                                                </span>
                                                                <span class="m-nav__link-title m-topbar__language-text m-nav__link-text">EUR</span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item m-nav__item--active">
                                                            <a href="javascript:confirmSwitch('Currency', '{{$customer_currency}}', 'GBP')" class="m-nav__link m-nav__link--active">
                                                                <span class="m-nav__link-icon">
                                                                    <i class="fa fa-pound-sign"></i>
                                                                </span>
                                                                <span class="m-nav__link-title m-topbar__language-text m-nav__link-text">GBP</span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                
                                <li class="m-nav__item m-topbar__languages m-dropdown m-dropdown--small m-dropdown--arrow m-dropdown--align-right m-dropdown--mobile-full-width" m-dropdown-toggle="click" aria-expanded="true">
                                    <a href="#" class="m-nav__link m-dropdown__toggle">
                                        <span class="m-nav__link-text">
                                            <img width="26px" style="border-radius: 50% !important;" class="m-topbar__language-selected-img" src="/assets/app/media/img/flags/{{$current_flag}}">	
                                        </span>
                                    </a>
                                    <div class="m-dropdown__wrapper" style="z-index: 101;">
                                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust" style="left: auto; right: 5px;"></span>
                                        <div class="m-dropdown__inner">
                                            <div class="m-dropdown__header m--align-center">
                                                <span class="m-dropdown__header-subtitle">Select your language</span>
                                            </div>
                                            
                                            <div class="m-dropdown__body">
                                                <div class="m-dropdown__content">
                                                    <ul class="m-nav m-nav--skin-light">
                                                        <li class="m-nav__item m-nav__item--active">
                                                            <a href="javascript:switchLanguage('de');" class="m-nav__link m-nav__link--active">
                                                                <span class="m-nav__link-icon">
                                                                    <img style="border-radius: 50% !important; width:22px" class="m-topbar__language-img" src="/assets/app/media/img/flags/de.png">
                                                                </span>
                                                                <span class="m-nav__link-title m-topbar__language-text m-nav__link-text">Deutsch</span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item m-nav__item--active">
                                                            <a href="javascript:switchLanguage('en');" class="m-nav__link m-nav__link--active">
                                                                <span class="m-nav__link-icon">
                                                                    <img style="border-radius: 50% !important; width:22px" class="m-topbar__language-img" src="/assets/app/media/img/flags/gb.png">
                                                                </span>
                                                                <span class="m-nav__link-title m-topbar__language-text m-nav__link-text">English</span>
                                                            </a>
                                                        </li>
                                                        <li class="m-nav__item m-nav__item--active">
                                                            <a href="javascript:switchLanguage('fr');" class="m-nav__link m-nav__link--active">
                                                                <span class="m-nav__link-icon">
                                                                    <img style="border-radius: 50% !important; width:22px" class="m-topbar__language-img" src="/assets/app/media/img/flags/fr.png">
                                                                </span>
                                                                <span class="m-nav__link-title m-topbar__language-text m-nav__link-text">Français</span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="m-nav__item m-topbar__user-profile m-dropdown m-dropdown--medium m-dropdown--arrow m-dropdown--align-right m-dropdown--mobile-full-width" m-dropdown-toggle="click">
                                    <a href="#" class="m-nav__link m-dropdown__toggle">
                                        <span class="m-topbar__welcome">Welcome &nbsp;</span>
                                        <span class="m-topbar__username">{{$customer_setup_config['caseHandlerName']}}</span>
                                    </a>
                                    <div class="m-dropdown__wrapper" style="z-index: 101;">
                                        <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust" style="left: auto; right: 5px;"></span>
                                        <div class="m-dropdown__inner">
                                            <div class="m-dropdown__inner">
                                                <div class="m-dropdown__header m--align-center">
                                                    <div class="m-card-user m-card-user--skin-light">
                                                        <div class="m-card-user__details">
                                                            <span class="m-card-user__name m--font-weight-500">
                                                                {{$customer_setup_config['caseHandlerName']}}
                                                            </span>
                                                            <a href="" class="m-card-user__email m--font-weight-300 m-link">
                                                                {{$customer_setup_config['caseHandlerEmail']}}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="m-dropdown__body">
                                                    <div class="m-dropdown__content">
                                                        <ul class="m-nav m-nav--skin-light">
                                                            <!--
                                                            <li class="m-nav__section m--hide">
                                                                <span class="m-nav__section-text">
                                                                    Section
                                                                </span>
                                                            </li>
                                                            <li class="m-nav__item">
                                                                <a href="profile.html" class="m-nav__link">
                                                                    <i class="m-nav__link-icon flaticon-share"></i>
                                                                    <span class="m-nav__link-title">
                                                                        <span class="m-nav__link-wrap">
                                                                            <span class="m-nav__link-text">
                                                                                Role : {{$customer_setup_config['userRole']}}
                                                                            </span>
                                                                        </span>
                                                                    </span>
                                                                </a>
                                                            </li>
                                                            <li class="m-nav__item">
                                                                <a href="profile.html" class="m-nav__link">
                                                                    <i class="m-nav__link-icon flaticon-profile-1"></i>
                                                                    <span class="m-nav__link-title">
                                                                        <span class="m-nav__link-wrap">
                                                                            <span class="m-nav__link-text">
                                                                                Case Status : OPEN
                                                                            </span>
                                                                        </span>
                                                                    </span>
                                                                </a>
                                                            </li>
                                                            <li class="m-nav__separator m-nav__separator--fit"></li> -->
                                                            <li class="m-nav__item">
                                                                <a href="{{config('app.api_url')}}/admin" class="btn m-btn--pill    btn-secondary m-btn m-btn--custom m-btn--label-brand m-btn--bolder">
                                                                    Back to Reseller Portal
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>      
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="m-header__bottom">
        <div class="m-container m-container--fluid m-container--full-height m-page__container">
            <div class="m-stack m-stack--ver m-stack--desktop">
                <!-- begin::Horizontal Menu -->
                @include ('partials.horizontal-menu')
                <!-- end::Horizontal Menu -->
            </div>
        </div>
    </div>
    <!--begin::Modal-->
    <div class="modal fade" id="switch_notify_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form id="switchForm" class="form-lang" method="post">
                        <div class="modal-header">
                            <h5 class="modal-title">CAUTION</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" >
                            <div id="switchContent"></div>
                            <input type="hidden" id="contentChange" name="" value=""/>
                            {{ csrf_field() }}
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-info">Continue</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
        <!--end::Modal-->
</header>
<!-- end::Header -->