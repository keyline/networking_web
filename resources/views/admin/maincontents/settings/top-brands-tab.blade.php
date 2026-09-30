@php($topBrands = \App\Services\TopBrandsService::settings())
<div class="tab-pane fade pt-3" id="tab12">
  <form method="POST" action="{{ url('admin/top-brands-settings') }}">
    @csrf
    <div class="mb-4">
      <h4>Top Brands on the app home screen</h4>
      <p class="text-muted">Choose how businesses are ranked. Businesses with activity in the selected period come first; any remaining places are filled alphabetically.</p>
    </div>

    <div class="row mb-3">
      <label for="top_brands_metric" class="col-md-4 col-lg-3 col-form-label">Rank businesses by</label>
      <div class="col-md-8 col-lg-9">
        <select name="top_brands_metric" id="top_brands_metric" class="form-select">
          @foreach(\App\Services\TopBrandsService::METRICS as $value => $label)
            <option value="{{ $value }}" @selected($topBrands['metric'] === $value)>{{ $label }}</option>
          @endforeach
        </select>
        <small class="text-muted d-block mt-1">
          Visits: profile views in the app. References received: leads other members passed to the business.
          References given: leads the business owner passed to others. Enquiries: all enquiries received.
        </small>
      </div>
    </div>

    <div class="row mb-3">
      <label for="top_brands_days" class="col-md-4 col-lg-3 col-form-label">Period</label>
      <div class="col-md-8 col-lg-9">
        <select name="top_brands_days" id="top_brands_days" class="form-select">
          @foreach(\App\Services\TopBrandsService::PERIODS as $value => $label)
            <option value="{{ $value }}" @selected($topBrands['days'] === $value)>{{ $label }}</option>
          @endforeach
        </select>
        <small class="text-muted d-block mt-1">Not used for Alphabetical.</small>
      </div>
    </div>

    <div class="row mb-3">
      <label for="top_brands_limit" class="col-md-4 col-lg-3 col-form-label">Number of brands</label>
      <div class="col-md-8 col-lg-9">
        <input type="number" name="top_brands_limit" id="top_brands_limit" class="form-control"
               min="3" max="30" value="{{ $topBrands['limit'] }}">
        <small class="text-muted d-block mt-1">Between 3 and 30. Multiples of 3 fill the app's grid evenly.</small>
      </div>
    </div>

    <div class="text-center">
      <button type="submit" class="btn btn-primary">Save Top Brands settings</button>
    </div>
  </form>
</div>
