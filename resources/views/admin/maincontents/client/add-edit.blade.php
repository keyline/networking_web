<?php
use App\Helpers\Helper;
$controllerRoute                = $module['controller_route'];
?>
<script>
   let autocomplete;
   let address1Field;
   let address2Field;
   let postalField;

   function initAutocomplete() {
   address1Field = document.querySelector("#address1");
   address2Field = document.querySelector("#street_no1");
   postalField = document.querySelector("#zipcode1");
   autocomplete = new google.maps.places.Autocomplete(address1Field, {
   componentRestrictions: { country: ["in"] },
   fields: ["address_components", "geometry", "formatted_address"],
   types: ["address"],
   });
   address1Field.focus();
   autocomplete.addListener("place_changed", fillInAddress);
   }

   function fillInAddress() {
   const place = autocomplete.getPlace();
   let address1 = "";
   let postcode = "";
   for (const component of place.address_components) {
   const componentType = component.types[0];
   switch (componentType) {
     case "postal_code": {
       postcode = `${component.long_name}${postcode}`;
       break;
     }
     case "postal_code_suffix": {
       postcode = `${postcode}-${component.long_name}`;
       break;
     }
     case "street_number": {
       document.querySelector("#street_no1").value = component.long_name;
       break;
     }
     case "route": {
       document.querySelector("#locality1").value = component.long_name;
       break;
     }
     case "locality": {
       document.querySelector("#city1").value = component.long_name;
       break;
     }
     case "administrative_area_level_1": {
       document.querySelector("#state1").value = component.short_name;
       break;
     }
     case "country":
       document.querySelector("#country1").value = component.short_name;
       break;
    }
   }
   address1Field.value = place.formatted_address;
   postalField.value = postcode;
   document.querySelector("#lat1").value = place.geometry.location.lat();
   document.querySelector("#lng1").value = place.geometry.location.lng();
   address2Field.focus();
   }
   window.initAutocomplete = initAutocomplete;
</script>
<div class="pagetitle">
  <h1><?=$page_header?></h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=url('admin/dashboard')?>">Home</a></li>
      <li class="breadcrumb-item active"><a href="<?=url('admin/' . $controllerRoute .'/'.$slug. '/list/')?>"><?=ucfirst($slug)?> List</a></li>
      <li class="breadcrumb-item active"><?=$page_header?></li>
    </ol>
  </nav>
</div><!-- End Page Title -->
<section class="section profile">
  <div class="row">
    <div class="col-xl-12">
      @if(session('success_message'))
        <div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show autohide" role="alert">
          {{ session('success_message') }}
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
      @if(session('error_message'))
        <div class="alert alert-danger bg-danger text-light border-0 alert-dismissible fade show autohide" role="alert">
          {{ session('error_message') }}
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
    </div>
    <?php
    if($row){
      $client_type_id         = $row->client_type_id;
      $name                   = $row->name;
      $email                  = $row->email;
      $alt_email              = $row->alt_email;
      $phone                  = $row->phone;
      $whatsapp_no            = $row->whatsapp_no;
      $short_bio              = $row->short_bio;
      $district_id            = $row->district_id;
      $address                = $row->address;
      $country                = $row->country;
      $state                  = $row->state;
      $city                   = $row->city;
      $locality               = $row->locality;
      $street_no              = $row->street_no;
      $zipcode                = $row->zipcode;
      $latitude               = $row->latitude;
      $longitude              = $row->longitude;
      $profile_image          = $row->profile_image;
    } else {
      $client_type_id         = '';
      $name                   = '';
      $email                  = '';
      $alt_email              = '';
      $phone                  = '';
      $whatsapp_no            = '';
      $short_bio              = '';
      $district_id            = '';
      $address                = '';
      $country                = 101;
      $state                  = 41;
      $city                   = '';
      $locality               = '';
      $street_no              = '';
      $zipcode                = '';
      $latitude               = '';
      $longitude              = '';
      $profile_image          = '';
    }
    ?>
    <div class="col-xl-12">
      <div class="card">
        <div class="card-body pt-3">
        @if ($errors->any())
          <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
          </div>
          @endif
          <span class="text-danger">Star (*) marks fields are mandatory</span>
          <form method="POST" action="" enctype="multipart/form-data">
            @csrf
            <div class="row mb-3">
              <label for="client_type" class="col-md-2 col-lg-2 col-form-label">Client Type</label>
              <div class="col-md-10 col-lg-10">
                <input type="text" name="client_type" class="form-control" id="client_type" value="<?=$slug?>" readonly>
              </div>
            </div>
            <div class="row mb-3">
              <label for="name" class="col-md-2 col-lg-2 col-form-label">Name <span class="text-danger">*</span></label>
              <div class="col-md-10 col-lg-10">
                <input type="text" name="name" class="form-control" id="name" value="<?=$name?>" required>
              </div>
            </div>
            <div class="row mb-3">
              <label for="email" class="col-md-2 col-lg-2 col-form-label">Email</label>
              <div class="col-md-10 col-lg-10">
                <input type="email" name="email" class="form-control" id="email" value="<?=$email?>">
              </div>
            </div>
            <div class="row mb-3">
              <label for="alt_email" class="col-md-2 col-lg-2 col-form-label">Alternate Email</label>
              <div class="col-md-10 col-lg-10">
                <input type="alt_email" name="alt_email" class="form-control" id="alt_email" value="<?=$alt_email?>">
              </div>
            </div>
            <div class="row mb-3">
              <label for="phone" class="col-md-2 col-lg-2 col-form-label">Phone <span class="text-danger">*</span></label>
              <div class="col-md-10 col-lg-10">
                <input type="text" name="phone" class="form-control" maxlength="10" id="phone" value="<?=$phone?>" required>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-2 col-lg-2"></div>
              <div class="col-md-10 col-lg-10">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" id="sameAsPhone">
                  <label for="sameAsPhone" class="form-check-label">Same as Phone Number</label>
                </div>
              </div>
            </div>
            <div class="row mb-3">
              <label for="whatsapp_no" class="col-md-2 col-lg-2 col-form-label">Whatsapp No</label>
              <div class="col-md-10 col-lg-10">
                <input type="text" name="whatsapp_no" class="form-control" id="whatsapp_no" value="<?=$whatsapp_no?>">
              </div>
            </div>
            <div class="row mb-3">
              <label for="short_bio" class="col-md-2 col-lg-2 col-form-label">Short Bio</label>
              <div class="col-md-10 col-lg-10">
                  <textarea name="short_bio" class="form-control" rows="5"><?=$short_bio?></textarea>
              </div>
            </div>
            <div class="row mb-3">
                <label for="district_id" class="col-md-2 col-lg-2 col-form-label">District</label>
                <div class="col-md-10 col-lg-10">
                  <select name="district_id" class="form-control" id="district_id" required>
                    <option value="" selected>Select District</option>
                    @if ($districts)
                      @foreach ($districts as $district)
                        <option value="{{ $district->id }}" <?=(($district->id == $district_id)?'selected':'')?>>{{ $district->name }}</option>
                      @endforeach
                    @endif
                  </select>
                </div>
            </div>

            <div class="row mb-3">
              <label for="address" class="col-md-2 col-lg-2 col-form-label">Address <span class="text-danger">*</span></label>
              <div class="col-md-10 col-lg-10">
                  <input type="text" name="address" id="address1" class="form-control" value="<?=$address?>" required>
              </div>
            </div>
            <div class="row mb-3">
              <label for="country" class="col-md-2 col-lg-2 col-form-label">Country</label>
              <div class="col-md-10 col-lg-10">
                  <input type="text" name="country" id="country1" class="form-control" value="<?=$country?>">
              </div>
            </div>
            <div class="row mb-3">
              <label for="state" class="col-md-2 col-lg-2 col-form-label">State <span class="text-danger">*</span></label>
              <div class="col-md-10 col-lg-10">
                  <input type="text" name="state" id="state1" class="form-control" value="<?=$state?>" required>
              </div>
            </div>
            <div class="row mb-3">
              <label for="city" class="col-md-2 col-lg-2 col-form-label">City</label>
              <div class="col-md-10 col-lg-10">
                  <input type="text" name="city" id="city1" class="form-control" value="<?=$city?>">
              </div>
            </div>
            <div class="row mb-3">
              <label for="locality" class="col-md-2 col-lg-2 col-form-label">Locality</label>
              <div class="col-md-10 col-lg-10">
                  <input type="text" name="locality" id="locality1" class="form-control" value="<?=$locality?>">
              </div>
            </div>
            <div class="row mb-3">
              <label for="street_no" class="col-md-2 col-lg-2 col-form-label">Street No.</label>
              <div class="col-md-10 col-lg-10">
                  <input type="text" name="street_no" id="street_no1" class="form-control" value="<?=$street_no?>">
              </div>
            </div>
            <div class="row mb-3">
              <label for="zipcode" class="col-md-2 col-lg-2 col-form-label">Zipcode <span class="text-danger">*</span></label>
              <div class="col-md-10 col-lg-10">
                  <input type="text" name="zipcode" id="zipcode1" class="form-control" value="<?=$zipcode?>" required>
              </div>
            </div>
            <div class="row mb-3">
              <label for="latitude" class="col-md-2 col-lg-2 col-form-label">Latitude</label>
              <div class="col-md-10 col-lg-10">
                  <input type="text" name="latitude" id="lat1" class="form-control" value="<?=$latitude?>">
              </div>
            </div>
            <div class="row mb-3">
              <label for="longitude" class="col-md-2 col-lg-2 col-form-label">Longitude</label>
              <div class="col-md-10 col-lg-10">
                  <input type="text" name="longitude" id="lng1" class="form-control" value="<?=$longitude?>">
              </div>
            </div>

            <div class="row mb-3">
              <label for="profile_image" class="col-md-2 col-lg-2 col-form-label">Profile Image</label>
              <div class="col-md-10 col-lg-10">
                <input type="file" name="profile_image" class="form-control" id="profile_image">
                <small class="text-info">* Only JPG, JPEG, ICO, SVG, PNG, WEBP files are allowed</small><br>
                <?php if($profile_image != ''){?>
                  <img src="<?=env('UPLOADS_URL').'client/'.$profile_image?>" class="img-thumbnail" alt="<?=$name?>" style="width: 150px; height: 150px; margin-top: 10px;">
                <?php } else {?>
                  <img src="<?=env('NO_IMAGE')?>" alt="<?=$name?>" class="img-thumbnail" style="width: 150px; height: 150px; margin-top: 10px;">
                <?php }?>
              </div>
            </div>
            <div class="text-center">
              <button type="submit" class="btn btn-primary"><?=(($row)?'Save':'Add')?></button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBMbNCogNokCwVmJCRfefB6iCYUWv28LjQ&libraries=places&callback=initAutocomplete&libraries=places&v=weekly"></script>
<script>
  document.getElementById('sameAsPhone').addEventListener('change', function () {
    const phoneField = document.getElementById('phone');
    const whatsappField = document.getElementById('whatsapp_no');

    if (this.checked) {
      whatsappField.value = phoneField.value;
      whatsappField.setAttribute('readonly', true); // Make the field readonly when autofilled
    } else {
      whatsappField.value = '';
      whatsappField.removeAttribute('readonly'); // Allow editing when unchecked
    }
  });

  // Ensure WhatsApp number updates when phone number changes and checkbox is checked
  document.getElementById('phone').addEventListener('input', function () {
    const sameAsPhoneCheckbox = document.getElementById('sameAsPhone');
    const whatsappField = document.getElementById('whatsapp_no');
    if (sameAsPhoneCheckbox.checked) {
      whatsappField.value = this.value;
    }
  });
</script>
