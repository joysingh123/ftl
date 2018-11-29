@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="text-center">
        Contact Import
    </h2>

    @if ( Session::has('success') )
    <div class="alert alert-success alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
            <span class="sr-only">Close</span>
        </button>
        <strong>{{ Session::get('success') }}</strong>
    </div>
    @endif

    @if ( Session::has('error') )
    <div class="alert alert-danger alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
            <span class="sr-only">Close</span>
        </button>
        <strong>{{ Session::get('error') }}</strong>
    </div>
    @endif

    @if (count($errors) > 0)
    <div class="alert alert-danger">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
        <div>
            @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
            @endforeach
        </div>
    </div>
    @endif

    <form action="{{ route('contactimport') }}" method="POST" enctype="multipart/form-data">
        {{ csrf_field() }}
        Choose your xls File : [
        Full Name,
        First Name,
        Last Name,
        Title,
        Company, 
        Experience, 
        Location,
        Company Url
        ]
        Optional : [
        Contact Country
        ] <input type="file" name="file" class="form-control">
        Tag : <input type="text" name="sheet_tag" class="form-control">

        <input type="submit" class="btn btn-primary btn-lg" style="margin-top: 3%">
    </form>
    <br>
    @if($sheet_data->count() <= 0)
    <div class="container">
        <h2>Instruction</h2>   
        <ul>
            <li>Excel Sheet Should have column 
                <span class="excel-column">[
                    Full Name,
                    First Name,
                    Last Name,
                    Title,
                    Company, 
                    Experience, 
                    Location,
                    Company Url
                    ] .
                </span>
            </li>
            <li>Excel Sheet optional column 
                <span class="excel-column">[
                    Contact Country
                    ] .
                </span>
            </li>
            <li>Excel Sheet should not have more then one sheet .</li>
            <li>Excel Sheet should have contains max 20,000 records.</li>
        </ul>
    </div>
    @endif
    @if($sheet_data->count() > 0)
    @if($sheet_data->first()->download == 'no')
    <div class="container">
        <p>Current Sheet Progress</p>
        <ul class="progress-indicator">
            <li class="{{$data_progress['Contact Uploading']}}">
                <span class="bubble"></span>
                <?php echo $completion_progress['Contact Uploading']; ?>
                Contact Uploading
            </li>
            <li class="{{$data_progress['Contact Added']}}">
                <span class="bubble"></span>
                <?php echo $completion_progress['Contact Added']; ?>
                Contact Added
            </li>
            <li class="{{$data_progress['Under Processing']}}">
                <span class="bubble"></span>
                <?php echo $completion_progress['Under Processing']; ?>
                Under Processing
            </li>
            <li class="{{$data_progress['Completed']}}">
                <span class="bubble"></span>
                <?php echo $completion_progress['Completed']; ?>
                Completed
            </li>
        </ul>
    </div>
    @endif
    @endif
    @if($sheet_data->count() > 0)
<br>
<div class="container">
    <h2>Sheet Status</h2><span><b>U.P.</b> => Under Processing, <b>D.N.F.</b> => Domain Not Found, <b>C.N.F</b> => Company Not Found</span>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Total</th>
                <th>Processed</th>
                <th>valid</th>
                <th>U.P.</th>
                <th>D.N.F.</th>
                <th>C.N.F.</th>
                <th>Time</th>
                <th>Status</th>
                <th>Download</th>
                <th>Re-Process</th>
                <th>Mark Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sheet_data AS $k=>$sh)
            
            <?php 
            $processed = 0;
            $valid = 0;
            $dnf = 0;
            $cnf = 0;
            if(isset($sheet_stats[$sh->ID]) && count($sheet_stats[$sh->ID]) > 0){
                $dnf = (isset($sheet_stats[$sh->ID]['domain not found'])) ? $sheet_stats[$sh->ID]['domain not found'] : 0;
                $cnf = (isset($sheet_stats[$sh->ID]['company not found'])) ? $sheet_stats[$sh->ID]['company not found'] : 0;
                $valid = (isset($sheet_stats[$sh->ID]['valid'])) ? $sheet_stats[$sh->ID]['valid'] : 0;
                $under_p = (isset($sheet_stats[$sh->ID]['under processing'])) ? $sheet_stats[$sh->ID]['under processing'] : 0;
                $processed = $sh->Total_Count - ($dnf + $cnf);
            }
            
            ?>
            <tr>
                <td>{{$k+1}}</td>
                <td>{{$sh->Sheet_Name}}</td>
                <td>{{$sh->Total_Count}}</td>
                <td>{{$processed}}</td>
                <td>{{$valid}} </td>
                <td>{{$under_p}}</td>
                <td>{{$dnf}} <a href="/exportcontactdnf/{{$sh->ID}}"><i class="fa fa-download" aria-hidden="true"></i></a></td>
                <td>{{$cnf}} <a href="/exportcontactcnf/{{$sh->ID}}"><i class="fa fa-download" aria-hidden="true"></i></a></td>
                <td><?php echo  \App\Helpers\UtilString::estimated_time($sh->Total_Count, $estimated_time); ?></td>
                <td>{{$sh->Status}}</td>
                @if($sh->Status == 'Completed')
                <td><a href="/exportcontactdata/{{$sh->ID}}"><i class="fa fa-download" aria-hidden="true"></i></a></td>
                @else
                <td>In Progress</td>
                @endif
                <td><input type="button" {{($sh->Status != 'Completed') ? 'disabled' : '' }} onclick="reprocesssheet('<?php echo $sh->ID; ?>')" value ='re-process' class="btn btn-primary btn-sm" style="margin-top: 3%;"></td>
                <td>
                    @if($sh->Status == 'Completed')
                        <input type="button" onclick="unmarkcomplete('<?php echo $sh->ID; ?>')" value ='Unmark Complete' class="btn btn-primary btn-sm" style="margin-top: 3%;">
                    @else
                        <input type="button" onclick="markcomplete('<?php echo $sh->ID; ?>')" value ='Mark Complete' class="btn btn-primary btn-sm" style="margin-top: 3%;">
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
</div>
@endsection