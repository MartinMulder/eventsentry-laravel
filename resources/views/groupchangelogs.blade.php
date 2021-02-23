@extends('layouts.app')

@section('content')
<table class="table">
	<tr>
		<th>dateTime</th>
		<th>action</th>
		<th>targetAccount</th>
		<th>sourceComputer</th>
		<th>targetDomain</th>
		<th>eventID</th>
		<th>callerDomain</th>
		<th>details</th>
		<th>targetAccountID</th>
		<th>eventNumber</th>
		<th>computer</th>
		<th>sourceIP</th>
		<th>callerAccount</th>
	</tr>
		
	@foreach( $logs['results'] as $log)
		<tr>
			<td>{{$log['dateTime']}}</td>
			<td>{{$log['action']}}</td>
			<td>{{$log['targetAccount']}}</td>
			<td>{{$log['sourceComputer']}}</td>
			<td>{{$log['targetDomain']}}</td>
			<td>{{$log['eventID']}}</td>
			<td>{{$log['callerDomain']}}</td>
			<td>{{$log['details']}}</td>
			<td>{{$log['targetAccountID']}}</td>
			<td>{{$log['eventNumber']}}</td>
			<td>{{$log['computer']}}</td>
			<td>{{$log['sourceIP']}}</td>
			<td>{{$log['callerAccount']}}</td>	
		</tr>
	@endforeach
</table>
@endsection