# Overtime Summary Footer Implementation Plan

> **For Antigravity:** REQUIRED WORKFLOW: Use `.agent/workflows/execute-plan.md` to execute this plan in single-flow mode.

**Goal:** Add a transparent calculation summary footer to the overtime table that visually breaks down the total payable amount calculation based on duty types.

**Architecture:** We will add a `<tfoot>` section to the existing overtime table. This footer will dynamically tally the number of "Workday Duty", "Dayoff/Holiday", and "Eid Special Duty" shifts. It will display the Per Day rate, the Multiplying Factors, and Sub-Totals for each category. We will update the existing JavaScript `calculateAmount` and `updateGrandTotal` functions to update these new footer cells in real-time.

**Tech Stack:** Laravel, Bootstrap 5, jQuery

---

### Task 1: Update UI View and JavaScript

**Files:**
- `[ ]` Update Blade template `resources/views/personnel/overtimes/index.blade.php`
- `[ ]` Add basic feature test assertion in `tests/Feature/OvertimeTest.php`

**Step 1: Write the failing test**

```php
// Add to: tests/Feature/OvertimeTest.php
public function test_overtime_index_displays_calculation_summary_footer()
{
    $admin = \App\Models\User::factory()->create(['role_id' => \App\Models\Role::where('name', 'HR Admin')->first()->id]);
    $employee = \App\Models\Employee::factory()->create();

    $response = $this->actingAs($admin)->get(route('overtimes.index', ['employee_id' => $employee->id, 'month' => '05', 'year' => '2026']));
    
    $response->assertStatus(200);
    $response->assertSee('Multiplying Factor');
    $response->assertSee('Sub-Total');
}
```

**Step 2: Run test to verify it fails**

Run: `php.exe artisan test --filter test_overtime_index_displays_calculation_summary_footer`
Expected: FAIL asserting that the response contains "Multiplying Factor".

**Step 3: Write minimal implementation**

In `resources/views/personnel/overtimes/index.blade.php`, add the `<tfoot>` right after the closing `</tbody>`:

```html
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr class="border-top border-2">
                                        <td colspan="4" class="text-end fw-bold">Total hours/Shift</td>
                                        <td class="text-center fw-bold" id="summary_workday_count">0</td>
                                        <td class="text-center fw-bold" id="summary_holiday_count">0</td>
                                        <td class="text-center fw-bold" id="summary_eid_count">0</td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end text-muted small">Rate per Shift/Eid Special</td>
                                        <td class="text-center text-muted small" id="summary_workday_rate">0.00</td>
                                        <td class="text-center text-muted small" id="summary_holiday_rate">0.00</td>
                                        <td class="text-center text-muted small" id="summary_eid_rate">0.00</td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end text-muted small">Multiplying Factor</td>
                                        <td class="text-center text-muted small">1</td>
                                        <td class="text-center text-muted small">2</td>
                                        <td class="text-center text-muted small">3</td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Sub-Total</td>
                                        <td class="text-center fw-bold text-success" id="summary_workday_subtotal">0.00</td>
                                        <td class="text-center fw-bold text-success" id="summary_holiday_subtotal">0.00</td>
                                        <td class="text-center fw-bold text-success" id="summary_eid_subtotal">0.00</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
```

In `resources/views/personnel/overtimes/index.blade.php`, update the `updateGrandTotal()` JavaScript function:

```javascript
            function updateGrandTotal() {
                let total = 0;
                let workdayCount = 0;
                let holidayCount = 0;
                let eidCount = 0;
                
                $('[id^="amount_"]').each(function() {
                    const val = parseFloat($(this).text().replace(/,/g, '')) || 0;
                    total += val;
                });
                
                $('.ot-check[name*="[workday_plus_5]"]:checked').each(function() { workdayCount++; });
                $('.ot-check[name*="[holiday_plus_5]"]:checked').each(function() { holidayCount++; });
                $('.ot-check[name*="[eid_duty]"]:checked').each(function() { eidCount++; });

                $('#total_payable_display').text(total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' BDT');
                
                $('#summary_workday_count').text(workdayCount);
                $('#summary_holiday_count').text(holidayCount);
                $('#summary_eid_count').text(eidCount);
                
                $('#summary_workday_rate').text(fullShiftIncome.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#summary_holiday_rate').text(fullShiftIncome.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#summary_eid_rate').text(fullShiftIncome.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                
                $('#summary_workday_subtotal').text((fullShiftIncome * 1 * workdayCount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#summary_holiday_subtotal').text((fullShiftIncome * 2 * holidayCount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#summary_eid_subtotal').text((fullShiftIncome * 3 * eidCount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            }
```

**Step 4: Run test to verify it passes**

Run: `php.exe artisan test --filter test_overtime_index_displays_calculation_summary_footer`
Expected: PASS


