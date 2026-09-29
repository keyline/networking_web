<?php
use App\Helpers\Helper;
use App\Models\ClientType;
use App\Models\District;
use App\Models\Role;

$controllerRoute = $module['controller_route'];
?>
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

                    <div class="dt-responsive table-responsive">
                        <table id="<?= count($rows) > 0 ? 'simpletable' : '' ?>"
                            class="table table-striped table-bordered nowrap">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>

                                    <!-- <th scope="col">Client Type</th> -->
                                    <th scope="col">Business Name</th>
                                    <th scope="col">Category</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Address</th>
                                    <th scope="col">License Start </th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>

                                @if (count($rows) > 0)
                                    @php $sl = 1; @endphp
                                    @foreach ($rows as $row)

                                        <tr>
                                            <th scope="row">{{ $sl++ }}</th>
                                            <td>
                                                <a
                                                    href="{{ url('admin/' . $controllerRoute . '/' . $slug . '/info-edit/' . Helper::encoded($row['cmp_id'])) }}">{{ wordwrap($row['name'], 40, "\n", true) }}</a>
                                                <br>
                                                <small><span style="color: #ccc8c8"> Owner :</span>
                                                    {{ $row['owner_name'] }}</small>
                                            </td>
                                            <td>{{ $row['tagCount'] }}</td>
                                            <td>{{ $row['email'] }}</td>
                                            <td>{{ $row['phone'] }}</td>
                                            <td>{{ $row['address1'] }} <br> {{ $row['district'] }} ,
                                                {{ $row['pincode'] }} </td>
                                            <td>
                                                {{ $row['license_start_datetime'] }}
                                            </td>

                                            <td>
                                                {{-- <style>
                                                    .form-check-input.sm {
                                                        width: 40px;
                                                        height: 25px;
                                                    }

                                                    .form-check-input.sm:checked {
                                                        background-color: #28a745; /* Success color */
                                                        border-color: #28a745;
                                                    }
                                                </style>

                                                <div class="form-check form-switch">
                                                    <input class="form-check-input sm" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                                                </div> --}}

                                                <livewire:admin.component.company-status-toggle :cmpId="$row['cmp_id']" :key="$row['cmp_id']" />

                                            </td>
                                            <td>
                                                <a href="{{ url('admin/' . $controllerRoute . '/' . $slug . '/info-edit/' . Helper::encoded($row['cmp_id'])) }}"
                                                    class="btn btn-outline-primary btn-sm" title="Edit Business">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            </td>

                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="9" style="text-align: center; color: red;">No Records Found !!!
                                        </td>
                                    </tr>
                                @endif

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
