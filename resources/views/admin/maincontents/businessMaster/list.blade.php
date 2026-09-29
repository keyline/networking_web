<div class="pagetitle">
    <h1><?= $page_header ?></h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
            <li class="breadcrumb-item active"><?= $page_header ?></li>
        </ol>
    </nav>
</div>
<!-- End Page Title -->
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
                    <h5 class="card-title">
                        <a href="{{ route($module['controller_route'] . '.create') }}"
                            class="btn btn-outline-success btn-sm">Add
                            <?= $module['title'] ?></a>
                    </h5>
                    <div class="dt-responsive table-responsive">
                        <table id="simpletable" class="table table-striped table-bordered nowrap">
                            <thead>
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($rows)>0){ $sl=1; foreach($rows as $row){?>
                                <tr>
                                    <td> {{ $sl++ }} </td>
                                    <td> {{ $row->btm_name }} </td>

                                    <td>
                                        <a href="{{ route($module['controller_route'] . '.edit', Helper::encoded($row->btm_id)) }}"
                                            class="btn btn-outline-primary btn-sm"
                                            title="Edit <?= $module['title'] ?>"><i class="fa fa-edit"></i>
                                        </a>



                                        <form action="{{ route($module['controller_route'] . '.destroy', Helper::encoded($row->btm_id)) }}" method="POST" style="display: inline;" onsubmit="return confirm('Do You Want To Delete This {{ $module['title'] }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete {{ $module['title'] }}">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>



                                    </td>
                                </tr>
                                <?php } } else {?>
                                <tr>
                                    <td colspan="3" style="text-align: center;color: red;">No Records Found !!!</td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
