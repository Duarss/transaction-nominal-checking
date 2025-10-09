<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme" style="background-color: #DDEBF7 !important;)">

  <div class="app-brand demo mx-0 px-0">
    <a href="#" class="app-brand-link">
      <a href="#" class="app-brand-link">
        <span class="app-brand-logo demo"><img src="{{ asset('tktw/logo-avian.png') }}" alt="Logo" class="mx-4" width="10%"></span>
      </a>

      <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
        <i class="bx bx-chevron-left bx-sm align-middle"></i>
      </a>
  </div>
  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    <li class="menu-header small text-uppercase"><span class="menu-header-text">MAIN</span></li>
    <x-menu label="Dashboard" icon="bxs bx-home" route-name="dashboard" />
    @can('viewAny', App\Models\ActionLog::class)
      <x-menu label="List Action Logs" icon="bx bx-list-ul" route-name="mainActivityLog.index" />
    @endcan
    @can('viewAny', App\Models\DropsizeRecommendation::class)
      <li class="menu-item has-sub {{ request()->routeIs('dropsizeRecommendation.index', 'dropsizeChangeLog.index') ? 'open active' : '' }}">
        <a href="#" class="menu-link menu-toggle">
          <i class="menu-icon bx bx-message-square-dots"></i>
          <div>Product Dropsize</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item">
            <x-menu label="List Rekomendasi" icon="bx-list-ul" route-name="dropsizeRecommendation.index" :active="request()->routeIs('dropsizeRecommendation.index') ? 'active' : ''" />
          </li>
          <li class="menu-item">
            <x-menu label="List Change Logs" icon="bx-category" route-name="dropsizeChangeLog.index" :active="request()->routeIs('dropsizeChangeLog.index') ? 'active' : ''" />
          </li>
        </ul>
      </li>
    @endcan
    <li class="menu-header small text-uppercase"><span class="menu-header-text">MASTERS</span></li>
    @can('viewAny', \App\Models\Branch::class)
      <x-menu label="Master Branch" icon="bxs bx-git-branch" route-name="masterBranch.index" />
    @endcan
    @can('viewAny', App\Models\Store::class)
      <x-menu label="Master Store" icon="bxs bx-building" route-name="masterStore.index" />
    @endcan
    @can('viewAny', App\Models\Transaction::class)
      <x-menu label="Master Transaction" icon="bxs bx-transfer-alt" route-name="masterTransaction.index" />
    @endcan
    @can('viewAny', App\Models\Warehouse::class)
      <li class="menu-item has-sub {{ request()->routeIs('masterWarehouse.index', 'masterWarehouse.product-stock-details') ? 'active' : '' }}">
        <a href="#" class="menu-link menu-toggle">
          <i class="menu-icon bx bx-box"></i>
          <div>Master Warehouse</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item {{ request()->routeIs('masterWarehouse.index') ? 'active' : '' }}">
            <x-menu 
              label="List Warehouses" 
              icon="bx-list-ul" 
              route-name="masterWarehouse.index" 
              :active="request()->routeIs('masterWarehouse.index') ? 'active' : ''" 
            />
          </li>
          @if(request()->routeIs('masterWarehouse.product-stock-details') && request()->route('warehouse'))
            <li class="menu-item {{ request()->routeIs('masterWarehouse.product-stock-details') ? 'active' : '' }}">
              <x-menu 
                label="Detail Product Stock Warehouse" 
                icon="bx-detail" 
                route-name="masterWarehouse.product-stock-details" 
                :route-params="['warehouse' => request()->route('warehouse')]" 
                :active="request()->routeIs('masterWarehouse.product-stock-details') ? 'active' : ''" 
              />
            </li>
          @endif
        </ul>
      </li>
    @endcan
    @can('viewAny', App\Models\Product::class)
      {{-- <x-menu label="Master Product" icon="bx-cube" route-name="masterProduct.index" /> --}}
        <li class="menu-item has-sub {{ request()->routeIs('masterProduct.index', 'masterType.index') ? 'open active' : '' }}">
          <a href="#" class="menu-link menu-toggle">
            <i class="menu-icon bx bx-cube"></i>
            <div>Master Product</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item">
              <x-menu label="List Products" icon="bx-list-ul" route-name="masterProduct.index" :active="request()->routeIs('masterProduct.index') ? 'active' : ''" />
            </li>
            <li class="menu-item">
              <x-menu label="List Tipe Products" icon="bx-category" route-name="masterType.index" :active="request()->routeIs('masterType.index') ? 'active' : ''" />
            </li>
          </ul>
        </li>
    @endcan
  </ul>
  <h6 style="margin-right: 10px;margin-bottom: 1px">©{{ date('Y') }} v{{ config('app.version') }}</h6>
</aside>
