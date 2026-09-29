<?php
use App\Helpers\Helper;
$controllerRoute = $module['controller_route'];
?>
<div class="pagetitle">
    <h1><?= $page_header ?></h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
            <li class="breadcrumb-item active"><a
                    href="<?= url('admin/' . $controllerRoute . '/' . $slug . '/list/') ?>"><?= ucfirst($slug) ?>
                    List</a></li>
            <li class="breadcrumb-item active"><?= $page_header ?></li>
        </ol>
    </nav>
</div><!-- End Page Title -->
<section class="section profile">
    <div class="row">
        <div class="col-xl-12">
            @if (session('success_message'))
                <div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show autohide"
                    role="alert">
                    {{ session('success_message') }}
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                        aria-label="Close"></button>
                </div>
            @endif
            @if (session('error_message'))
                <div class="alert alert-danger bg-danger text-light border-0 alert-dismissible fade show autohide"
                    role="alert">
                    {{ session('error_message') }}
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                        aria-label="Close"></button>
                </div>
            @endif
        </div>
        <?php
        if (is_null($row)) {
            $currentDate = date('Y-m-d 00:00:00');
            $update_id = 0;
            $cmpd_cmp_id = '';
            $cmpd_company_regn_no = '';
            $cmpd_name = '';
            $cmpd_description = '';
            $cmpd_email = '';
            $cmpd_alternate_email = '';
            $cmpd_phone = '';
            $cmpd_whatsapp_no = '';
            $cmpd_logo = '';
            $cmpd_address1 = '';
            $cmpd_address2 = '';
            $cmpd_address3 = '';
            $cmpd_estd_year = '';
            $cmpd_district = '';
            $cmpd_country = '';
            $cmpd_state = '';
            $cmpd_pincode = '';
            $cmpd_license_start_datetime = $currentDate;
            $cmpd_license_end_datetime = $currentDate;
            $cmpd_license_ref = '';
            $cmpd_last_renewal_date = $currentDate;
            $logo_image = '';
            $selectedCategory = '';
        } else {
            $categoryInfo = $row->companies->categories->first();

            $update_id = $row->cmpd_id;
            $cmpd_cmp_id = $row->cmpd_cmp_id;
            $cmpd_company_regn_no = $row->cmpd_company_regn_no ?? '';
            $cmpd_name = $row->cmpd_name ?? '';
            $cmpd_description = $row->cmpd_description ?? '';
            $cmpd_email = $row->cmpd_email ?? '';
            $cmpd_alternate_email = $row->cmpd_alternate_email ?? '';
            $cmpd_phone = $row->cmpd_phone ?? '';
            $cmpd_whatsapp_no = $row->cmpd_whatsapp_no ?? '';
            $cmpd_logo = $row->cmpd_logo ?? '';
            $cmpd_address1 = $row->cmpd_address1 ?? '';
            $cmpd_address2 = $row->cmpd_address2 ?? '';
            $cmpd_address3 = $row->cmpd_address3 ?? '';
            $cmpd_estd_year = $row->cmpd_estd_year ?? '';

            $cmpd_country = $row->cmpd_country ?? '';
            $cmpd_state = $row->cmpd_state ?? '';
            $cmpd_district = $row->cmpd_district ?? '';

            $cmpd_pincode = $row->cmpd_pincode ?? '';
            $cmpd_license_start_datetime = $row->cmpd_license_start_datetime ?? '';
            $cmpd_license_end_datetime = $row->cmpd_license_end_datetime ?? '';
            $cmpd_license_ref = $row->cmpd_license_ref ?? '';
            $cmpd_last_renewal_date = $row->cmpd_last_renewal_date ?? '';
            $logo_image = $row->cmpd_logo ?? '';
            $selectedCategory = $categoryInfo ? $categoryInfo->bcm_id : '';
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
                            <label for="client_type" class="col-md-2 col-lg-2 col-form-label">Registration No</label>
                            <div class="col-md-10 col-lg-10">
                                <input type="text" name="regn_no" class="form-control" id="client_type"
                                    value="<?= $cmpd_company_regn_no ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="name" class="col-md-2 col-lg-2 col-form-label">Business Name
                                <span class="text-danger">*</span>
                            </label>
                            <div class="col-md-10 col-lg-10">
                                <input type="text" name="name" class="form-control" id="name"
                                    value="<?= $cmpd_name ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="name" class="col-md-2 col-lg-2 col-form-label">Description
                                <span class="text-danger">*</span>
                            </label>
                            <div class="col-md-10 col-lg-10">
                                {{-- <input type="text" name="description" class="form-control" id="description"
                                    value="<?= $cmpd_description ?>" required> --}}


                                <textarea id="description" name="description" style="width: 100%" rows="5" required>{{ $cmpd_description }}</textarea>
                                <small>
                                    <p class="text-info">The description field must not be greater than 500 characters.</p>
                                </small>
                            </div>
                        </div>


                        <div class="row mb-3">
                            <label for="email" class="col-md-2 col-lg-2 col-form-label">Business Email</label>
                            <div class="col-md-10 col-lg-10">
                                <input type="email" name="email" class="form-control" id="email"
                                    value="<?= $cmpd_email ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="alt_email" class="col-md-2 col-lg-2 col-form-label">Alternate Email</label>
                            <div class="col-md-10 col-lg-10">
                                <input type="alt_email" name="alternate_email" class="form-control" id="alt_email"
                                    value="<?= $cmpd_alternate_email ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="phone" class="col-md-2 col-lg-2 col-form-label">Business Phone<span
                                    class="text-danger">*</span></label>
                            <div class="col-md-10 col-lg-10">
                                <input type="text" name="phone" class="form-control" maxlength="10" id="phone"
                                    value="<?= $cmpd_phone ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="whatsapp_no" class="col-md-2 col-lg-2 col-form-label">Whatsapp No</label>
                            <div class="col-md-10 col-lg-10">
                                <input type="text" name="whatsapp_no" class="form-control" id="whatsapp_no"
                                    value="<?= $cmpd_whatsapp_no ?>">
                            </div>
                        </div>


                        <div class="row mb-3">
                            <label for="whatsapp_no" class="col-md-2 col-lg-2 col-form-label">Address line 1 </label>
                            <div class="col-md-10 col-lg-10">
                                <input type="text" name="address1" class="form-control" id="address1"
                                    value="<?= $cmpd_address1 ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="whatsapp_no" class="col-md-2 col-lg-2 col-form-label">Address line 2 </label>
                            <div class="col-md-10 col-lg-10">
                                <input type="text" name="address2" class="form-control" id="address2"
                                    value="<?= $cmpd_address2 ?>">
                            </div>
                        </div>


                        <div class="row mb-3">
                            <label for="whatsapp_no" class="col-md-2 col-lg-2 col-form-label">Address line 3 </label>
                            <div class="col-md-10 col-lg-10">
                                <input type="text" name="address3" class="form-control" id="address3"
                                    value="<?= $cmpd_address3 ?>">
                            </div>
                        </div>


                        <div class="row mb-3">
                            <label for="whatsapp_no" class="col-md-2 col-lg-2 col-form-label"> Established Year
                            </label>
                            <div class="col-md-10 col-lg-10">
                                <input type="text" name="estd_year" class="form-control" id="estd_year"
                                    value="<?= $cmpd_estd_year ?>">
                            </div>
                        </div>



                        <!-- Render the Livewire dependent dropdown -->

                        <livewire:admin.layout.dependent-dropdown c="{{ $cmpd_country }}" s="{{ $cmpd_state }}"
                            d="{{ $cmpd_district }}" />




                        <div class="row mb-3">
                            <label for="whatsapp_no" class="col-md-2 col-lg-2 col-form-label">Pincode</label>
                            <div class="col-md-10 col-lg-10">
                                <input type="text" name="pincode" class="form-control" id="cmpd_pincode"
                                    value="<?= $cmpd_pincode ?>">
                            </div>
                        </div>


                        <div class="row mb-3">
                            <label for="whatsapp_no" class="col-md-2 col-lg-2 col-form-label">license
                                Reference</label>
                            <div class="col-md-10 col-lg-10">
                                <input type="text" name="license_ref" class="form-control" id="cmpd_license_ref"
                                    value="<?= $cmpd_license_ref ?>">
                            </div>
                        </div>



                        <div class="row mb-3">
                            <label for="whatsapp_no" class="col-md-2 col-lg-2 col-form-label">License
                                Start</label>
                            <div class="col-md-10 col-lg-10">
                                <input type="date" name="license_start" class="form-control" id="license_start"
                                    value="{{ date('Y-m-d', strtotime($cmpd_license_start_datetime)) }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="whatsapp_no" class="col-md-2 col-lg-2 col-form-label">License End</label>
                            <div class="col-md-10 col-lg-10">
                                <input type="date" name="license_end" class="form-control" id="license_end"
                                    value="{{ date('Y-m-d', strtotime($cmpd_license_end_datetime)) }}">
                            </div>
                        </div>


                        <div class="row mb-3">
                            <label for="whatsapp_no" class="col-md-2 col-lg-2 col-form-label">Renewal
                                Start</label>
                            <div class="col-md-10 col-lg-10">
                                <input type="date" name="renewal_date" class="form-control" id="renewal_date"
                                    value="{{ date('Y-m-d', strtotime($cmpd_last_renewal_date)) }}">
                            </div>
                        </div>




                        <div class="row mb-3">
                            <label for="category_id" class="col-md-2 col-lg-2 col-form-label">Category</label>
                            <div class="col-md-10 col-lg-10">
                                <select name="category_id" class="form-control" id="category_id" required>
                                    <option value="" selected disabled>Select</option>
                                    @if ($category)
                                        @foreach ($category as $row)
                                            <option value="{{ $row->bcm_id }}" @selected($row->bcm_id == $selectedCategory)>
                                                {{ $row->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>



                        <div class="row mb-3">
                            <label for="logo_image" class="col-md-2 col-lg-2 col-form-label">Logo Image</label>
                            <div class="col-md-10 col-lg-10">
                                <input type="file" name="logo" class="form-control" id="logo_image">
                                <small class="text-info">* jpeg,png,jpg files are
                                    allowed</small><br>
                                <?php if($logo_image != ''){?>
                                <img src="<?= env('UPLOADS_URL') . 'company/' . $logo_image ?>" class="img-thumbnail"
                                    alt="<?= $logo_image ?>" style="width: 150px; height: 150px; margin-top: 10px;">
                                <?php } else {?>
                                <img src="<?= env('NO_CATEGORY_IMAGE') ?>" alt="IMG" class="img-thumbnail"
                                    style="width: 150px; height: 150px; margin-top: 10px;">
                                <?php }?>
                            </div>
                        </div>
                        <div class="text-center">
                            <input type="hidden" name="id" value="{{ $update_id }}">
                            <button type="submit" class="btn btn-primary"><?= $row ? 'Save' : 'Add' ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
{{-- <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript"
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBMbNCogNokCwVmJCRfefB6iCYUWv28LjQ&libraries=places&callback=initAutocomplete&libraries=places&v=weekly">
</script> --}}
