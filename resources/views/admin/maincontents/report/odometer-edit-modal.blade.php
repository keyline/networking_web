<script>
   let autocompleteStart, autocompleteEnd;
   
   function initAutocomplete() {
   // Initialize autocomplete for start_address
   const startAddressField = document.querySelector("#start_address");
       autocompleteStart = new google.maps.places.Autocomplete(startAddressField, {
           componentRestrictions: { country: ["in"] },
           fields: ["address_components", "geometry", "formatted_address"],
           types: ["address"],
       });
       autocompleteStart.addListener("place_changed", () => fillInAddress(autocompleteStart, "start"));

       // Initialize autocomplete for end_address
       const endAddressField = document.querySelector("#end_address");
       autocompleteEnd = new google.maps.places.Autocomplete(endAddressField, {
           componentRestrictions: { country: ["in"] },
           fields: ["address_components", "geometry", "formatted_address"],
           types: ["address"],
       });
       autocompleteEnd.addListener("place_changed", () => fillInAddress(autocompleteEnd, "end"));
   }

   
   function fillInAddress(autocomplete, type) {
   const place = autocomplete.getPlace();
   let addressField = document.querySelector(`#${type}_address`);
    let latField = document.querySelector(`#${type}_lat`);
    let lngField = document.querySelector(`#${type}_lng`);
    let postalField = document.querySelector(`#${type}_postal`);
    if (!addressField) return;

    let address1 = "";
    let postcode = "";

    for (const component of place.address_components) {
        const componentType = component.types[0];
        switch (componentType) {
            case "postal_code":
                postcode = component.long_name;
                break;
            case "street_number":
            case "route":
            case "locality":
            case "administrative_area_level_1":
            case "country":
                address1 += `${component.long_name} `;
                break;
        }
    }
    ;

    // Set the values
    addressField.value = place.formatted_address;
    if (postalField) postalField.value = postcode;
    if (latField) latField.value = place.geometry.location.lat();
    if (lngField) lngField.value = place.geometry.location.lng();    
    

   }
   window.initAutocomplete = initAutocomplete;
</script>
 <div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">
    <div class="modal-header">
        <h4 class="modal-title" id="exampleModalLabel">Attendance & Odo Info : <?=$name?></h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">        
         <!-- Card 1 -->
         <div class="odo-card">
            <!-- <div class="card-header">DATE : 09/12/2024</div> -->
            <div class="card-body">
              <h6 class="mb-1"><u>ODO History</u></h6>
              <?php if($odometer_data){?>
                <div class="timeline-section mb-3" style="border: 1px solid #0096885e;padding: 10px;border-radius: 10px;background-color: #055e1303;">                  
                  <div class="row align-items-center">
                  <form action="<?php echo url('admin/report/odometer-store/'.$odometerId)?>" method="POST" enctype="multipart/form-data" class="w-100">
                    @csrf
                    <div class="row align-items-center">
                      <div class="col-sm-5">
                          <h5 class="text-success">START</h5>
                          <label for="start_image">Start Image:</label>
                          <input type="file" id="start_image" name="start_image" accept="image/*" class="form-control mb-3">
                          <!-- <img src="{{ $odometer_data['start_image'] }}" alt="start" style="width: 50px; height:50px; border-radius: 50%;"> -->
                          <?= Helper::generateLightboxImage($odometer_data['start_image'], 'Start', '50', '50', '', 'border-radius: 50%;') ?>
                          <br>
                          <label for="start_km"><i class="fa-solid fa-gauge me-2"></i>KM:</label>
                          <input type="text" id="start_km" name="start_km" value="{{ $odometer_data['start_km'] }}" class="form-control mb-3">
                          <label for="start_timestamp"><i class="fa-regular fa-clock me-2"></i>Start Timestamp:</label>
                          <input type="datetime-local" id="start_timestamp" name="start_timestamp" value="{{ date('Y-m-d H:i', strtotime($odometer_data['start_timestamp'])) }}" class="form-control mb-3">
                          <label for="start_address"><i class="fa-solid fa-location-dot me-2"></i>Start Address:</label>
                          <input type="text" id="start_address" name="start_address" value="{{ $odometer_data['start_address'] }}" class="form-control mb-3">
                          <input type="hidden" id="start_lat" name="start_lat">
                          <input type="hidden" id="start_lng" name="start_lng">
                          <input type="hidden" id="start_postal" name="start_postal">
                      </div>

                      <div class="col-sm-2 text-center">
                          <h5 class="text-info">DISTANCE</h5>
                          <div class="distance_box">
                              <i class="fa-solid fa-right-left"></i>
                              <div class="distance" id="travel_distance">{{ $odometer_data['travel_distance'] ?? 0 }} KM</div>
                              <input type="hidden" id="travel_distance_new" name="travel_distance" value="{{ $odometer_data['travel_distance'] ?? 0 }}">
                          </div>
                      </div>

                      <div class="col-sm-5">
                          <h5 class="text-danger">END</h5>
                          <label for="end_image">End Image:</label>
                          <input type="file" id="end_image" name="end_image" accept="image/*" class="form-control mb-3">
                          <!-- <img src="{{ $odometer_data['end_image'] }}" alt="end" style="width: 50px; height:50px; border-radius: 50%;"> -->
                          <?= Helper::generateLightboxImage($odometer_data['end_image'], 'End', '50', '50', '', 'border-radius: 50%;') ?>
                          <br>
                          <label for="end_km"><i class="fa-solid fa-gauge me-2"></i>KM:</label>
                          <input type="text" id="end_km" name="end_km" value="{{ $odometer_data['end_km'] }}" class="form-control mb-3">
                          <label for="end_timestamp"><i class="fa-regular fa-clock me-2"></i>End Timestamp:</label>
                          <input type="datetime-local" id="end_timestamp" name="end_timestamp" value="{{ date('Y-m-d H:i', strtotime($odometer_data['end_timestamp'])) }}" class="form-control mb-3">
                          <label for="end_address"><i class="fa-solid fa-location-dot me-2"></i>End Address:</label>
                          <input type="text" id="end_address" name="end_address" value="{{ $odometer_data['end_address'] }}" class="form-control mb-3">
                          <input type="hidden" id="end_lat" name="end_lat">
                          <input type="hidden" id="end_lng" name="end_lng">
                          <input type="hidden" id="end_postal" name="end_postal">
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-12 text-center mt-3">
                          <button type="submit" class="btn btn-primary">Submit</button>
                      </div>
                    </div>
                  </form>
                  </div>
                </div>
              <?php  } ?>
            </div>
        </div>
    </div>
  </div>
</div> 
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBMbNCogNokCwVmJCRfefB6iCYUWv28LjQ&libraries=places&callback=initAutocomplete&libraries=places&v=weekly"></script>
<script>
    document.getElementById('start_km').addEventListener('input', calculateDistance);
    document.getElementById('end_km').addEventListener('input', calculateDistance);

    function calculateDistance() {
        const startKm = parseFloat(document.getElementById('start_km').value) || 0;
        const endKm = parseFloat(document.getElementById('end_km').value) || 0;
        const distance = Math.max(0, endKm - startKm);
        document.getElementById('travel_distance').textContent = `${distance} KM`;
        document.getElementById('travel_distance_new').value = distance;
    }
</script>


<!-- <div class="modal-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">New message</h5>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="modal-body">
      <form>
        <div class="form-group">
          <label for="recipient-name" class="col-form-label">Recipient:</label>
          <input type="text" class="form-control" id="recipient-name">
        </div>
        <div class="form-group">
          <label for="message-text" class="col-form-label">Message:</label>
          <textarea class="form-control" id="message-text"></textarea>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      <button type="button" class="btn btn-primary">Send message</button>
    </div>
  </div>
</div> -->