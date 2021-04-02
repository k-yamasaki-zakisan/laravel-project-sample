<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
</head>
<body>
	<div>
		<p>下記の内容で申請されています。</p>
		<table style="border-collapse: collapse;">
			<thead>
				<tr>
					<th style="border: 1px solid silver; padding: 2px 5px;">申請日時</th>
					<th style="border: 1px solid silver; padding: 2px 5px;">担当者</th>
					<th style="border: 1px solid silver; padding: 2px 5px;">概要</th>
					<th style="border: 1px solid silver; padding: 2px 5px;">金額</th>
				</tr>
			</thead>
			<tbody>
				@foreach($ExpenseSummaries as $ExpenseSummary)
					<tr>
						<td style="border: 1px solid silver; padding: 2px 5px;">{{ $ExpenseSummary['requested_at'] ?? null }}</td>
						<td style="border: 1px solid silver; padding: 2px 5px;">{{ $ClientEmployees[$ExpenseSummary['client_employee_id']]['name'] ?? null }}</td>
						<td style="border: 1px solid silver; padding: 2px 5px;">{{ $ExpenseSummary['content'] ?? null }}</td>
						<td style="border: 1px solid silver; padding: 2px 5px;">{{ $ExpenseSummary['total_amount'] ?? null }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</body>
</html>
