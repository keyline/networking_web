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
                    {{-- <h5 class="card-title">
                        <a href="<?= url('admin/' . $controllerRoute . '/' . $slug . '/add/') ?>"
                            class="btn btn-outline-success btn-sm">Add <?= ucfirst($slug) ?></a>
                    </h5> --}}
                    <div class="dt-responsive table-responsive">





                        <table id="<?= count($rows) > 0 ? 'simpletable' : '' ?>"
                            class="table table-striped table-bordered nowrap">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>

                                    <!-- <th scope="col">Client Type</th> -->
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Address</th>

                                    @if ($client_type && $client_type->utm_id == 2)
                                        <th scope="col"> Business</th>
                                    @endif

                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>

                                @if (count($rows) > 0)
                                    @php $sl = 1; @endphp
                                    @foreach ($rows as $row)
                                        @php
                                            $is_seller = $row['um_utm_id'] == 2;
                                            $user = $row['user_detail'];

                                            $ud_addr = $is_seller ? $user['ud_business_addr_1'] : $user['ud_addr_1'];

                                        @endphp
                                        <tr>
                                            <th scope="row">{{ $sl++ }}</th>
                                            <td>{{ $user['ud_first_name'] }}</td>
                                            <td>{{ $row['um_email_id'] }}</td>
                                            <td>{{ $row['um_mobile_no'] }}</td>
                                            <td>{!! wordwrap($ud_addr, 25, '<br>') !!}</td>
                                            @if ($client_type && $client_type['utm_id'] == 2)
                                                <td>
                                                    {{-- <small><span> {{ $row['companies_map_count'] }} </span></small> --}}
                                                    <br>
                                                    @if (count($row['companies_map']))
                                                        @foreach ($row['companies_map'] as $innerRow)
                                                            {{-- <small><a
                                                                    href="{{ url('admin/' . $controllerRoute . '/business/info-edit/' . Helper::encoded($innerRow['companie']['cmpd_cmp_id'])) }}">
                                                                    {{ $innerRow['companie']['cmpd_name'] }}</a></small>


                                                        <span>
                                                            <livewire:admin.component.company-status-toggle :cmpId="$innerRow['companie']['cmpd_cmp_id']" :key="$innerRow['companie']['cmpd_cmp_id']" />
                                                        </span> --}}

                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <small>
                                                                <a href="{{ url('admin/' . $controllerRoute . '/business/info-edit/' . Helper::encoded($innerRow['companie']['cmpd_cmp_id'])) }}">
                                                                    {{ $innerRow['companie']['cmpd_name'] }}
                                                                </a>
                                                                @if (!empty($innerRow['companie']['public_slug']))
                                                                    <a href="{{ route('business.show', $innerRow['companie']['public_slug']) }}"
                                                                        target="_blank" rel="noopener"
                                                                        class="ms-2 text-primary"
                                                                        title="Open business page in a new tab"
                                                                        aria-label="Open {{ $innerRow['companie']['cmpd_name'] }} business page in a new tab">
                                                                        <i class="fa fa-external-link"></i>
                                                                    </a>
                                                                @endif
                                                            </small>
                                                            <livewire:admin.component.company-status-toggle :cmpId="$innerRow['companie']['cmpd_cmp_id']" :key="$innerRow['companie']['cmpd_cmp_id']" />
                                                        </div>

                                                        @endforeach
                                                    @else
                                                        <a href="{{ url('admin/' . $controllerRoute . '/business/info-edit/0/' . $row['um_id']) }}"
                                                            class="btn btn-outline-primary btn-sm"
                                                            title="Edit Business">
                                                            <i class="fa fa-add"></i>
                                                        </a>
                                                    @endif


                                                </td>
                                            @endif
                                            <td>
                                                {{-- <a href="{{ url('admin/' . $controllerRoute . '/' . $slug . '/edit/' . Helper::encoded($row->um_id)) }}"
                                                    class="btn btn-outline-primary btn-sm"
                                                    title="Edit {{ ucfirst($slug) }}">
                                                    <i class="fa fa-edit"></i>
                                                </a>

                                                <a href="{{ url('admin/' . $controllerRoute . '/' . $slug . '/delete/' . Helper::encoded($row->um_id)) }}"
                                                    class="btn btn-outline-danger btn-sm"
                                                    title="Delete {{ ucfirst($slug) }}"
                                                    onclick="return confirm('Do You Want To Delete This {{ ucfirst($slug) }}');">
                                                    <i class="fa fa-trash"></i>
                                                </a> --}}

                                                {{-- Only status 2 can log in; 0/1 show the Activate button --}}
                                                @if ((int) $row['um_status'] === 2)
                                                    <a href="{{ url('admin/' . $controllerRoute . '/' . $slug . '/change-status/' . Helper::encoded($row['um_id'])) }}"
                                                        class="btn btn-outline-warning btn-sm"
                                                        title="Active - click to deactivate {{ ucfirst($slug) }}"
                                                        onclick="return confirm('Deactivate this {{ $slug }}? They will not be able to log in.');">
                                                        <i class="fa fa-times"></i> Deactivate
                                                    </a>
                                                @elseif ((int) $row['um_status'] === 1 && $client_type && (int) $client_type['utm_id'] === 2)
                                                    <form method="post" action="{{ route('admin.registrations.approve-member', $row['um_id']) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm"
                                                            title="Approve this member account"
                                                            onclick="return confirm('Approve this member account? Business approval remains separate.');">
                                                            <i class="fa fa-user-check"></i> Approve member
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ url('admin/' . $controllerRoute . '/' . $slug . '/change-status/' . Helper::encoded($row['um_id'])) }}"
                                                        class="btn btn-outline-success btn-sm"
                                                        title="Inactive - click to activate {{ ucfirst($slug) }}">
                                                        <i class="fa fa-check"></i> Activate
                                                    </a>
                                                @endif

                                                <a href="{{ url('admin/' . $controllerRoute . '/' . $slug . '/view_details/' . Helper::encoded($row['um_id'])) }}"
                                                    class="btn btn-outline-info btn-sm"
                                                    title="View Details {{ $module['title'] }}" target="_blank">
                                                    <i class="fa fa-info-circle"></i>
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
