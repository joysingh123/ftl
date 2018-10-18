@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="text-center">
        Company Import
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

    <form action="{{ route('companyimport') }}" method="POST" enctype="multipart/form-data">
        {{ csrf_field() }}
        Choose your xls File : [
        Linkedin Id, Linkedin url, Company Domain, Company Name, Company Type, Employee Count at LinkedIN, Industry, City, Postal Code, Employee Size, Country
        ] <input type="file" name="file" class="form-control">

        <input type="submit"  class="btn btn-primary btn-lg" style="margin-top: 3%">
    </form>
    @if(!Session::has('stats_data'))
    <br>
    <div class="container">
        <h2>Instruction</h2>   
        <ul>
            <li>Excel Sheet Should have column 
                <span class="excel-column">[
                    Linkedin Id, Linkedin url, Company Domain, Company Name, Company Type, Employee Count at LinkedIN, Industry, City, Postal Code, Employee Size, Country
                    ] .
                </span>
            </li>
            <li>Excel Sheet should not have more then one sheet .</li>
            <li>Excel Sheet should have contains max 20,000 records.</li>
        </ul>
    </div>
    @endif
    @if(Session::has('stats_data'))
    @php
    $stats_data = Session::get('stats_data')
    @endphp
    <br>
    <br>
    <div class="container">
        <h2>Companies Stats</h2>         
        <table class="table">
            <thead>
                <tr>
                    <th>New Inserted</th>
                    <th>Duplicate In Sheet</th>
                    <th>Already Exist</th>
                    <th>Domain Not Exist</th>
                    <th>Junk Found</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{$stats_data['inserted']}}</td>
                    <td>{{$stats_data['duplicate_in_sheet']}}</td>
                    <td>{{$stats_data['already_exist_in_db']}}</td>
                    <td>{{$stats_data['domain_not_exist']}}</td>
                    <td>{{$stats_data['junk_count']}}</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if(count($stats_data['domain_not_found']) > 0)
    <br>
    <div class="container">
        <h2>Domain Not Found</h2>         
        <table class="table">
            <thead>
                <tr>
                    <th>Linkedin Id</th>
                    <th>Linkedin Url</th>
                    <th>Company Domain</th>
                    <th>Company Name</th>
                    <th>Company Type</th>
                    <th>Employee Count At Linkedin</th>
                    <th>Industry</th>
                    <th>City</th>
                    <th>Postal Code</th>
                    <th>Employee Size</th>
                    <th>Country</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats_data['domain_not_found'] AS $c)
                <tr>
                    <td>{{$c['linkedin_id']}}</td>
                    <td>{{$c['linkedin_url']}}</td>
                    <td>{{$c['company_domain']}}</td>
                    <td>{{$c['company_name']}}</td>
                    <td>{{$c['company_type']}}</td>
                    <td>{{$c['employee_count_at_linkedin']}}</td>
                    <td>{{$c['industry']}}</td>
                    <td>{{$c['city']}}</td>
                    <td>{{$c['postal_code']}}</td>
                    <td>{{$c['employee_size']}}</td>
                    <td>{{$c['country']}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    @if(count($stats_data['junk_data_array']) > 0)
    <br>
    <div class="container">
        <h2>Junk Data</h2>         
        <table class="table">
            <thead>
                <tr>
                    <th>Linkedin Id</th>
                    <th>Linkedin Url</th>
                    <th>Company Domain</th>
                    <th>Company Name</th>
                    <th>Company Type</th>
                    <th>Employee Count At Linkedin</th>
                    <th>Industry</th>
                    <th>City</th>
                    <th>Postal Code</th>
                    <th>Employee Size</th>
                    <th>Country</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats_data['junk_data_array'] AS $c)
                <tr>
                    <td>{{$c['linkedin_id']}}</td>
                    <td>{{$c['linkedin_url']}}</td>
                    <td>{{$c['company_domain']}}</td>
                    <td>{{$c['company_name']}}</td>
                    <td>{{$c['company_type']}}</td>
                    <td>{{$c['employee_count_at_linkedin']}}</td>
                    <td>{{$c['industry']}}</td>
                    <td>{{$c['city']}}</td>
                    <td>{{$c['postal_code']}}</td>
                    <td>{{$c['employee_size']}}</td>
                    <td>{{$c['country']}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    @endif
</div>
@endsection