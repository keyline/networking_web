@section('extra_css')
@endsection
<div class="pagetitle">
    <h1><?= $page_header ?></h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
            <li class="breadcrumb-item active">
                <a href="{{ route($module['controller_route'] . '.index') }}"><?= $module['title'] ?>
                    List
                </a>
            </li>
            <li class="breadcrumb-item active"><?= $page_header ?></li>
        </ol>
    </nav>
</div>
<!-- End Page Title -->
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

        <div class="col-xl-12">
            <div class="card">
                <div class="card-body pt-3">
                    <form
                        action="{{ $row ? route($module['controller_route'] . '.update', Helper::encoded($row->im_id)) : route($module['controller_route'] . '.store') }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @if ($row)
                            @method('PATCH')
                        @else
                            @method('POST')
                        @endif
                        <div class="row mb-3">
                            <label for="name" class="col-md-2 col-lg-2 col-form-label">Name</label>
                            <div class="col-md-10 col-lg-10">
                                <input type="text" name="name" class="form-control" id="name"
                                    value="{{ $row ? $row->im_name : old('name') }}">
                                @error('name')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary"> {{ $row ? 'Save' : 'Add' }} </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@section('extra_js')
@endsection
