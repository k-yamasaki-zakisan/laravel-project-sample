<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
</head>
<body>
	<div>
		<table style="border-collapse: collapse;">
			<tbody>
				<tr>
					<th style="border: 1px solid silver; padding: 2px 5px; text-align: center;">TRCD端末名</th>
					<td style="border: 1px solid silver; padding: 2px 5px; text-align: center;">{{ $trcd_temrnal_name ?? null }}</td>
				</tr>
				<tr>
					<th style="border: 1px solid silver; padding: 2px 5px; text-align: center;">通知日時</th>
					<td style="border: 1px solid silver; padding: 2px 5px; text-align: center;">{{ $send_at ?? null }}</td>
				</tr>
			</tbody>
		</table>
		<p>以下の金種が不足しています。</p>
		<table style="border-collapse: collapse;">
			<thead>
				<tr>
					<th style="border: 1px solid silver; padding: 2px 5px;">金種</th>
					<th style="border: 1px solid silver; padding: 2px 5px;">残高</th>
					<th style="border: 1px solid silver; padding: 2px 5px;">枚数</th>
				</tr>
			</thead>
			<tbody>
				@foreach($summaries as $summary)
					<tr>
						<td style="border: 1px solid silver; padding: 2px 5px; text-align: right;">{{ $summary['display_name'] ?? null }}</td>
						<td style="border: 1px solid silver; padding: 2px 5px; text-align: right;">
							@isset($summary['amount_of_balance'])
								{{ number_format($summary['amount_of_balance']) }}円
							@endisset
						</td>
						<td style="border: 1px solid silver; padding: 2px 5px; text-align: right;">
							@isset($summary['number_of_balance'])
								{{ $summary['number_of_balance'] }}枚
							@endisset
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</body>
</html>
