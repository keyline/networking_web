<?php
use App\Helpers\Helper;
use App\Models\Admin;
use App\Models\ClientType;
use App\Models\Companies;
use App\Models\Country;
use App\Models\Employees;
use App\Models\EmployeeType;

$controllerRoute = $module['controller_route'];
$url_slug = $slug;
// dd($url_slug);
?>
<style>
    .lightbox .lb-nav {
        display: none !important;
    }
</style>
<div class="pagetitle">
    <h1><?= $page_header ?></h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
            <li class="breadcrumb-item active"><?= $page_header ?></li>
        </ol>
    </nav>
</div><!-- End Page Title -->
<section class="section">
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
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-bordered">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab1">Basic
                                Info</button>
                        </li>
                        @if (!is_null($business) && $business->isNotEmpty())
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab3">Business</button>
                            </li>
                        @endif
                        {{-- <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab5">Orders</button>
                        </li> --}}
                    </ul>
                    <div class="tab-content pt-2">
                        {{-- tab 1 --}}
                        <div class="tab-pane fade show active" id="tab1">

                            <table class="table table-striped table-bordered nowrap">
                                <tbody>

                                    <tr>
                                        <td>Client Type</td>
                                        <td> {{ $row->userType->utm_name ?? '' }} </td>
                                    </tr>
                                    <tr>
                                        <td>User No</td>
                                        <td> {{ $row->um_user_name ?? '' }} </td>
                                    </tr>
                                    <tr>
                                        <td>Name</td>
                                        <td> {{ $row->userDetail->ud_first_name ?? '' }}
                                            {{ $row->userDetail->ud_last_name ?? '' }} </td>
                                    </tr>
                                    <tr>
                                        <td>Email</td>
                                        <td> {{ $row->um_email_id ?? '' }} </td>
                                    </tr>

                                    <tr>
                                        <td>Phone</td>
                                        <td> {{ $row->um_mobile_no ?? '' }} </td>
                                    </tr>

                                    <tr>
                                        <td>Address</td>
                                        <td> {{ $row->userDetail->ud_addr_1 ?? '' }} </td>
                                    </tr>



                                    {{-- <tr>
                                        <td>Profile Image</td>
                                        <td>
                                            <?php /* if (!empty($row->profile_image)) {?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?> ?>
                                            <!-- <img src="<?= env('UPLOADS_URL') . 'user/' . $row->profile_image ?>" alt="<?= $row->name ?>" style="width: 150px; height: 150px; margin-top: 10px;"> -->
                                            <?= Helper::generateLightboxImage(env('UPLOADS_URL') . 'user/' . $row->profile_image, $row->name) ?>
                                            <?php } else {?>
                                            <!-- <img src="<?= env('NO_IMAGE') ?>" alt="<?= $row->name ?>" class="img-thumbnail" style="width: 150px; height: 150px; margin-top: 10px;"> -->
                                            <?= Helper::generateLightboxImage(env('NO_IMAGE'), $row->name) ?>
                                            <?php }  */?>
                                        </td>
                                    </tr> --}}
                                </tbody>
                            </table>
                        </div>
                        {{-- tab 2 --}}
                        <div class="tab-pane fade pt-3" id="tab3">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="dt-responsive table-responsive">
                                            <table class="table table-striped table-bordered nowrap">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">#</th>
                                                        <th scope="col">Company Name</th>
                                                        <th scope="col">Description</th>
                                                        <th scope="col">Address</th>
                                                        <th scope="col">License Start</th>
                                                        <th scope="col">License End</th>
                                                        <th scope="col">Renewal Date</th>
                                                        <!-- <th scope="col">Action</th> -->
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if (!is_null($business) && $business->isNotEmpty())
                                                        @foreach ($business as $key => $item)
                                                            @if (!is_null($item->details))
                                                                <tr>
                                                                    <td> {{ ++$key }} </td>
                                                                    <td> {{ $item->details->cmpd_name ?? '' }} </td>
                                                                    <td> {{ $item->details->cmpd_description ?? '' }}
                                                                    </td>
                                                                    <td> {{ $item->details->cmpd_address1 ?? '' }} <br>
                                                                        {{ $item->details->cmpd_district ?? '' }} ,
                                                                        {{ $item->details->cmpd_pincode ?? '' }}
                                                                    </td>
                                                                    <td> {{ date('d-m-Y', strtotime($item->details->cmpd_license_start_datetime)) }}
                                                                    </td>
                                                                    <td> {{ date('d-m-Y', strtotime($item->details->cmpd_license_end_datetime)) }}
                                                                    </td>
                                                                    <td> {{ date('d-m-Y', strtotime($item->details->cmpd_last_renewal_date)) }}
                                                                    </td>

                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="tab-pane fade pt-3" id="tab5">

                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered  modal-dialog-scrollable mx-auto modal-lg">
            <div class="modal-content" id="modalBody">
            </div>
        </div>
    </div>
</section>
<script>
    function clientwiseorderList(orderId, name, slug) {
        $('#modalBody').html('');
        // Construct the dynamic URL using the provided slug
        const requestUrl = `<?php echo url('admin/clients/'); ?>/${slug}/clientwiseorderListRecords`;
        //  alert(requestUrl);
        $.ajax({
            url: requestUrl,
            type: 'GET',
            data: {
                orderId: orderId,
                name: name
            },
            dataType: 'html',
            success: function(response) {
                console.log(JSON.parse(response).html);
                $('#modalBody').html(JSON.parse(response).html);
                $('#myModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error('Error fetching modal content:', error);
            }
        });
    }
</script>
