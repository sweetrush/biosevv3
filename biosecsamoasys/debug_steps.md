Debugging Steps for Cargo Seizure Form:

1. Open http://localhost:8081/voyagement.php
2. Click on "Cargo Seizure" tab
3. Open browser console (F12)
4. Check for console messages about country loading
5. Wait 3 seconds for debug button to appear
6. Click red "Debug Country Dropdown" button
7. Try manually typing: debugCargoSeizureDropdown() in console
8. Check if dropdown shows countries when clicked

Expected Console Output:
- "loadCountryDropdownForCargoSeizure called"
- "Countries loaded successfully: 235"
- "Final dropdown options count: 236"
- Debug output showing country names