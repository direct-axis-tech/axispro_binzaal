<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<link rel="stylesheet" href="{{ base_path('resources/assets/sass/pdf.styles.css') }}">
		<style>
			<?php foreach ($styles as $style): ?>
			<?= e($style['selector']) ?> {
				<?= e($style['style']) ?>
			}
			<?php endforeach; ?>
		</style>
	</head>
	<body>
		<?php
		$ctr = 1;
		$no = 1;
		$total = [];
		$grandTotalSkip = 1;
		$currentGroupByData = [];
		$isOnSameGroup = true;

		foreach ($showTotalColumns as $column => $type) {
			$total[$column] = 0;
		}

		if ($showTotalColumns != []) {
			foreach ($columns as $colName => $colData) {
				if (!array_key_exists($colName, $showTotalColumns)) {
					$grandTotalSkip++;
				} else {
					break;
				}
			}
		}

        $grandTotalSkip = !$showNumColumn ? $grandTotalSkip - 1 : $grandTotalSkip;
		?>
		<div class="wrapper">
		    <div class="border-top ">
			    <div class="h1">
			        {{ $headers['title'] }}
			    </div>
    			@if ($showMeta)
				<table class="w-100">
					<tr>
						<td class="w-100">
							<table class="w-100">
								<tbody>
								@foreach($headers['meta'] as $name => $value)
									<tr>
										<td class="w-auto text-nowrap pr-4"><b>{{ $name }}</b></td>
										<td class="w-100">{{ ucwords($value) }}</td>
									</tr>
								@endforeach
								</tbody>
							</table>
						</td>
						<td class="w-auto align-top tex-nowrap">
							<table class="w-100">
								<tbody>
									<tr><td>{{ Str::after(config('app.url'), '://') }}</td></tr>
									<tr><td>{{ auth()->user()->name }}</td></tr>
								</tbody>
							</table>
						</td>
					</tr>
				</table>
				@endif
		    </div>
			<table class="w-100 table table-sm table-striped table-borderless thead-bordered">
				@if ($showHeader)
				<thead>
					<tr>
						@if ($showNumColumn)
							<th class="text-left">{{ __('laravel-report-generator::messages.no') }}</th>
						@endif
						@foreach ($columns as $colName => $colData)
							@if (array_key_exists($colName, $editColumns))
								<th class="{{ class_names([
									'text-left' => !isset($editColumns[$colName]['class']),
									$editColumns[$colName]['class'] ?? null,
								]) }}">{{ $colName }}</th>
							@else
								<th class="text-left">{{ $colName }}</th>
							@endif
						@endforeach
					</tr>
				</thead>
				@endif
				<?php
				$__env = isset($__env) ? $__env : null;
				?>
				@foreach($query->when($limit, function($qry) use($limit) { $qry->take($limit); })->cursor() as $result)
					<?php
						if ($limit != null && $ctr == $limit + 1) return false;
						if ($groupByArr) {
							$isOnSameGroup = true;
							foreach ($groupByArr as $groupBy) {
								if (is_object($columns[$groupBy]) && $columns[$groupBy] instanceof Closure) {
									$thisGroupByData[$groupBy] = $columns[$groupBy]($result);
								} else {
									$thisGroupByData[$groupBy] = $result->{$columns[$groupBy]};
								}


								if (isset($currentGroupByData[$groupBy])) {
									if ($thisGroupByData[$groupBy] != $currentGroupByData[$groupBy]) {
										$isOnSameGroup = false;
									}
								}

								$currentGroupByData[$groupBy] = $thisGroupByData[$groupBy];
							}

							if ($isOnSameGroup === false) {
								echo '<tr class="bg-gray-600 text-white">';
								if ($showNumColumn || $grandTotalSkip > 1) {
									echo '<td colspan="' . $grandTotalSkip . '"><b>'.__('laravel-report-generator::messages.grand_total').'</b></td>';
								}
								$dataFound = false;
								foreach ($columns as $colName => $colData) {
									if (array_key_exists($colName, $showTotalColumns)) {
										if ($showTotalColumns[$colName] == 'point') {
											echo '<td class="text-right"><b>' . number_format($total[$colName], 2, '.', ',') . '</b></td>';
										} else {
											echo '<td class="text-right"><b>' . strtoupper($showTotalColumns[$colName]) . ' ' . number_format($total[$colName], 2, '.', ',') . '</b></td>';
										}
										$dataFound = true;
									} else {
										if ($dataFound) {
											echo '<td></td>';
										}
									}
								}
								echo '</tr>';

								// Reset No, Reset Grand Total
								$no = 1;
								foreach ($showTotalColumns as $showTotalColumn => $type) {
									$total[$showTotalColumn] = 0;
								}
								$isOnSameGroup = true;
							}
						}
					?>
					<tr class="text-center">
						@if ($showNumColumn)
							<td class="text-left">{{ $no }}</td>
						@endif
						@foreach ($columns as $colName => $colData)
							<?php
								$class = 'text-left';
								// Check Edit Column to manipulate class & Data
								if (is_object($colData) && $colData instanceof Closure) {
									$generatedColData = $colData($result);
								} else {
									$generatedColData = $result->{$colData};
								}
								$displayedColValue = $generatedColData;
								if (array_key_exists($colName, $editColumns)) {
									if (isset($editColumns[$colName]['class'])) {
										$class = $editColumns[$colName]['class'];
									}

									if (isset($editColumns[$colName]['displayAs'])) {
										$displayAs = $editColumns[$colName]['displayAs'];
										if (is_object($displayAs) && $displayAs instanceof Closure) {
											$displayedColValue = $displayAs($result);
										} elseif (!(is_object($displayAs) && $displayAs instanceof Closure)) {
											$displayedColValue = $displayAs;
										}
									}
								}

								if (array_key_exists($colName, $showTotalColumns)) {
									$total[$colName] += $generatedColData;
								}
							?>
							<td class="{{ $class }}">{{ $displayedColValue }}</td>
						@endforeach
					</tr>
					<?php $ctr++; $no++; ?>
				@endforeach
				@if ($showTotalColumns != [] && $ctr > 1)
					<tr class="bg-black f-white">
						@if ($showNumColumn || $grandTotalSkip > 1)
							<td colspan="{{ $grandTotalSkip }}"><b>{{ __('laravel-report-generator::messages.grand_total') }}</b></td> {{-- For Number --}}
						@endif
						<?php $dataFound = false; ?>
						@foreach ($columns as $colName => $colData)
							@if (array_key_exists($colName, $showTotalColumns))
								<?php $dataFound = true; ?>
								@if ($showTotalColumns[$colName] == 'point')
									<td class="text-right"><b>{{ number_format($total[$colName], 2, '.', ',') }}</b></td>
								@else
									<td class="text-right"><b>{{ strtoupper($showTotalColumns[$colName]) }} {{ number_format($total[$colName], 2, '.', ',') }}</b></td>
								@endif
							@else
								@if ($dataFound)
									<td></td>
								@endif
							@endif
						@endforeach
					</tr>
				@endif
			</table>
		</div>
	    <script type="text/php">
			@if (strtolower($orientation) == 'portrait')
            if ( isset($pdf) ) {
                $pdf->page_text(30, ($pdf->get_height() - 26.89), __('laravel-report-generator::messages.printed_at', ['date' => date('d M Y H:i:s')]), null, 10);
                $pdf->page_text(($pdf->get_width() - 84), ($pdf->get_height() - 26.89), __('laravel-report-generator::messages.page_pdf'), null, 10);
            }
            @elseif (strtolower($orientation) == 'landscape')
            if ( isset($pdf) ) {
                $pdf->page_text(30, ($pdf->get_height() - 26.89), __('laravel-report-generator::messages.printed_at', ['date' => date('d M Y H:i:s')]), null, 10);
                $pdf->page_text(($pdf->get_width() - 84), ($pdf->get_height() - 26.89), __('laravel-report-generator::messages.page_pdf'), null, 10);
            }
            @endif
	    </script>
	</body>
</html>
