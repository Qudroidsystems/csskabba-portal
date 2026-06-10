                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TOTALS SUMMARY --}}
            <div class="totals-summary">
                TOTAL OBTAINED: {{ number_format($totals['obtained'] ?? 0, 1) }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                TOTAL OBTAINABLE: {{ $totals['obtainable'] ?? 0 }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                % OBTAINED: {{ $totals['percentage'] ?? 0 }}%
            </div>

            {{-- ============================================ --}}
            {{-- PROMOTION BADGE - Compact and fits within width --}}
            {{-- ============================================ --}}
            @if (!$isPromoTerm)
                <div class="promo-card promo-awaiting">
                    <div class="promo-title">Awaiting Final Term</div>
                    <div class="promo-message">Promotion will be assessed at the end of the academic year.</div>
                </div>
            @else
                <div class="promo-card {{ $badgeClass }}">
                    <div class="promo-title">{{ $statusLabel }}</div>

                    @if($ruleDisplay && $ruleDisplay !== 'null' && $ruleDisplay !== '')
                        <div class="promo-rule">{{ $ruleDisplay }}</div>
                    @endif

                    @if($promoStatus === 'promoted')
                        <div class="promo-message">
                            @if($promoTotal > 0)
                                Passed {{ $promoPassed }}/{{ $promoTotal }} compulsory subject(s)
                            @else
                                Met all promotion requirements
                            @endif
                        </div>
                    @endif

                    @if($promoStatus === 'trial')
                        <div class="promo-message">Promoted conditionally - needs improvement</div>
                    @endif

                    @if($promoStatus === 'see_principal')
                        <div class="promo-message">Parents must see the Principal for discussion</div>
                    @endif

                    @if(($promoStatus === 'repeated' || $promoStatus === 'repeat') && !empty($promoFailed))
                        <div class="promo-message">
                            Failed: {{ collect($promoFailed)->pluck('subject')->filter()->implode(', ') }}
                        </div>
                    @endif

                    @if($reqAvg !== null && $actAvg !== null)
                        <div class="promo-average">
                            Average: {{ number_format($actAvg, 1) }}%
                            @if($reqAvg) (Required: {{ number_format($reqAvg, 1) }}%) @endif
                            @if($promoStatus === 'promoted') ✓ @endif
                        </div>
                    @endif
                </div>
            @endif

            {{-- ATTENDANCE BOX --}}
            @if($showAnyAttendance)
            <div class="attendance-box">
                <div class="attendance-box-header">📅 Attendance Record — {{ $term }}</div>

                @if(!$attFound)
                    <div style="padding:6px 10px;font-size:9px;color:#6b7280;text-align:center;background:#f9fafb;">
                        No attendance record available for this term.
                    </div>
                @else
                    <div class="attendance-grid">
                        @if(in_array('attendance_total_days', $columnsToShow))
                        <div class="att-cell">
                            <span class="att-label">School Days</span>
                            <span class="att-value">{{ $attendance['total_school_days'] ?? 0 }}</span>
                        </div>
                        @endif
                        @if(in_array('attendance_days_present', $columnsToShow))
                        <div class="att-cell">
                            <span class="att-label">Present</span>
                            <span class="att-value att-ok">{{ $attendance['days_present'] ?? 0 }}</span>
                        </div>
                        @endif
                        @if(in_array('attendance_days_absent', $columnsToShow))
                        <div class="att-cell">
                            <span class="att-label">Absent</span>
                            <span class="att-value {{ ($attendance['days_absent'] ?? 0) > 0 ? 'att-warn' : 'att-ok' }}">
                                {{ $attendance['days_absent'] ?? 0 }}
                            </span>
                        </div>
                        @endif
                        @if(in_array('attendance_days_late', $columnsToShow))
                        <div class="att-cell">
                            <span class="att-label">Late</span>
                            <span class="att-value {{ ($attendance['days_late'] ?? 0) > 0 ? 'att-warn' : 'att-ok' }}">
                                {{ $attendance['days_late'] ?? 0 }}
                            </span>
                        </div>
                        @endif
                        @if(in_array('attendance_sick_leave', $columnsToShow))
                        <div class="att-cell">
                            <span class="att-label">Sick</span>
                            <span class="att-value">{{ $attendance['days_sick_leave'] ?? 0 }}</span>
                        </div>
                        @endif
                        @if(in_array('attendance_excused', $columnsToShow))
                        <div class="att-cell">
                            <span class="att-label">Excused</span>
                            <span class="att-value">{{ $attendance['days_excused'] ?? 0 }}</span>
                        </div>
                        @endif
                        @if(in_array('attendance_percentage', $columnsToShow))
                        <div class="att-cell" style="min-width:70px;">
                            <span class="att-label">Attendance %</span>
                            <span class="att-value {{ $attWarn ? 'att-warn' : 'att-ok' }}">{{ $attPct }}%</span>
                        </div>
                        @endif
                    </div>

                    @if(in_array('attendance_percentage', $columnsToShow))
                    <div style="padding:3px 8px 5px;">
                        <div class="att-pct-bar-wrap">
                            <div class="att-pct-bar {{ $attWarn ? 'att-pct-warn' : '' }}"
                                 style="width:{{ min($attPct, 100) }}%;"></div>
                        </div>
                        <div style="font-size:8px;color:#6b7280;margin-top:2px;text-align:right;">
                            {{ $attWarn ? 'Below 75% — requires attention' : 'Satisfactory attendance' }}
                        </div>
                    </div>
                    @endif
                @endif
            </div>
            @endif

            {{-- REMARKS --}}
            <table class="remarks-table">
                <tbody>
                    <tr>
                        <td width="50%">
                            <div class="h6">Class Teacher's Remark</div>
                            <div>{{ $profile ? ($profile->classteachercomment ?? 'NO COMMENT') : 'NO COMMENT' }}</div>
                        </td>
                        <td width="50%">
                            <div class="h6">Principal's Remark</div>
                            <div>{{ $profile ? ($profile->principalscomment ?? 'NO COMMENT') : 'NO COMMENT' }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- BOTTOM STRIP --}}
            <div class="bottom-strip">
                <table>
                    <tr>
                        <td class="cell-qr">
                            <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Code">
                            <div class="qr-label">Scan for Verification</div>
                        </td>
                        <td class="cell-footer">
                            <div>
                                <strong>Issued:</strong>
                                <span class="text-dot-space2">{{ now()->format('jS F, Y') }}</span>
                            </div>
                            <div style="margin-top:3px;">
                                <strong>Collected by:</strong>
                                <span class="text-dot-space2">.......................................</span>
                            </div>
                            <div style="margin-top:3px;">
                                <strong>Next Term Begins:</strong>
                                <span class="text-dot-space2">
                                    @php
                                        $nextTerm = $schoolInfo->date_next_term_begins ?? null;
                                        echo $nextTerm
                                            ? \Carbon\Carbon::parse($nextTerm)->format('jS F, Y')
                                            : '........................';
                                    @endphp
                                </span>
                            </div>
                            <div class="powered-by">Powered by Qudroid Systems</div>
                        </td>
                        <td class="cell-stamp">
                            <img src="{{ $stampSrc }}" alt="School Stamp">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    @endforeach
</body>
</html>
