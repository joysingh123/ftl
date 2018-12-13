@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="text-center">
        Contact Domain Import
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

    <form action="{{ route('contactdomainimport') }}" method="POST" enctype="multipart/form-data">
        {{ csrf_field() }}
        Choose your xls File : [First Name, Last Name, Domain]
        Optional : [Full Name, Title, Industry, Company, Location, Employee Count, Country] 
        <input type="file" name="file" class="form-control">
        <input type="submit" class="btn btn-primary btn-lg" style="margin-top: 3%">
    </form>
    @if($sheet_data->count() <= 0)
    <br>
    <div class="container">
        <h2>Instruction</h2>   
        <ul>
            <li>Excel Sheet Should have column 
                <span class="excel-column">[
                    First Name,
                    Last Name,
                    Domain
                    ] .
                </span>
            </li>
            <li>Excel Sheet optional column 
                <span class="excel-column">[
                    Full Name, Title, Industry, Company, Location, Employee Count, Country
                    ] .
                </span>
            </li>
            <li>Excel Sheet should not have more then one sheet .</li>
            <li>Excel Sheet should have contains max 20,000 records.</li>
        </ul>
    </div>
    @endif
    @if($sheet_data->count() > 0)
    <br>
    <div class="container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Total</th>
                    <th>Processed</th>
                    <th>valid</th>
                    <th>Under Processing</th>
                    <th>Status</th>
                    <th>Download</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sheet_data AS $k=>$sh)
                <tr>
                    <td>{{$sh->id}}</td>
                    <td>{{$sh->sheet_name}}</td>
                    <td>{{$sh->total}}</td>
                    <td>{{$sheet_stats[$sh->id]['processed']}}</td>
                    <td>{{$sheet_stats[$sh->id]['valid']}}</td>
                    <td>{{$sheet_stats[$sh->id]['under processing']}}</td>
                    <td>{{$sh->status}}</td>
                    @if($sh->status == 'Completed')
                        <td><a href="/exportdomaincontactdata/{{$sh->id}}"><i class="fa fa-download" aria-hidden="true"></i></a></td>
                    @else
                        <td>In Progress</td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection