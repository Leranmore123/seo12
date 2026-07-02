<?php
require_once 'config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>How to Use - SEO 80/20 System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="style.css" rel="stylesheet">
<style>
.guide-section { border-radius: 12px; margin-bottom: 25px; border: 1px solid #e0e0e0; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
.guide-header { padding: 15px 20px; border-top-left-radius: 12px; border-top-right-radius: 12px; font-weight: bold; color: white; display: flex; align-items: center; }
.guide-body { padding: 20px; background-color: #ffffff; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; }
.badge-step { font-size: 14px; padding: 5px 12px; border-radius: 20px; }
.step-number { width: 30px; height: 30px; background: #0d6efd; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 10px; }
.gu-text { color: #555; line-height: 1.6; }
.eng-hint { font-size: 0.85rem; color: #6c757d; display: block; margin-top: 2px; }
.highlight-box { background: #f8f9fa; border-left: 4px solid #ffc107; padding: 12px; margin: 10px 0; border-radius: 0 8px 8px 0; font-size: 0.9rem; }
.screen-tag { background: #e9ecef; color: #495057; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 0.85rem; }
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="container py-4" style="max-width: 960px;">

  <div class="text-center mb-5">
    <h2 class="fw-bold text-primary"><i class="fas fa-graduation-cap me-2"></i>SEO 80/20 System — સંપૂર્ણ ગાઇડ (Easy Guide)</h2>
    <p class="text-muted">નાના બાળકને પણ આવડી જાય તેવું સરળ અને વિગતવાર માર્ગદર્શન (Detailed Step-by-Step Tutorial)</p>
    <div class="d-flex justify-content-center gap-2 mt-2">
      <span class="badge bg-primary px-3 py-2"><i class="fas fa-robot me-1"></i> ૮૦% ઓટોમેટિક</span>
      <span class="badge bg-success px-3 py-2"><i class="fas fa-user me-1"></i> ૨૦% મેન્યુઅલ</span>
      <span class="badge bg-dark px-3 py-2"><i class="fas fa-brain me-1"></i> ChatGPT Powered</span>
    </div>
  </div>

  <!-- SECTION 1: Client Profile Details Overview -->
  <div class="guide-section">
    <div class="guide-header bg-primary">
      <i class="fas fa-user-circle fa-lg me-2"></i> ૧. ક્લાયન્ટ પ્રોફાઇલ અને સામાન્ય વિગતો (Client Profile Details)
    </div>
    <div class="guide-body gu-text">
      <p>જ્યારે નવો ક્લાયન્ટ આવે ત્યારે તેના પ્રોજેક્ટના <strong>Client Profile</strong> માં નીચેની વિગતો સાચી ભરવી:</p>
      <ul>
        <li><strong>Business Name:</strong> દુકાન કે કંપનીનું સાચું નામ (દા.ત. <code>Learnmore Technologies</code>).</li>
        <li><strong>Contact Name:</strong> ક્લાયન્ટના માલિક અથવા સંપર્ક વ્યક્તિનું નામ.</li>
        <li><strong>Phone Number &amp; Email:</strong> ક્લાયન્ટનો ફોન નંબર અને ઈમેઈલ આઈડી. આ માહિતી લોકલ સર્ચ સ્કીમા અને કોન્ટેક્ટ પેજ માટે જરૂરી છે.</li>
      </ul>
    </div>
  </div>

  <!-- SECTION 3: Google Services Access Details -->
  <div class="guide-section">
    <div class="guide-header bg-dark">
      <i class="fab fa-google fa-lg me-2"></i> ૨. ગુગલ સર્વિસીઝ એક્સેસ વિગતો (Google Services Access Details)
    </div>
    <div class="guide-body gu-text">
      
      <!-- Google Search Console -->
      <div class="mb-4">
        <h5 class="text-primary fw-bold"><i class="fas fa-search me-2"></i>Google Search Console (GSC) Access</h5>
        <p><strong>આ શું છે?</strong> તમારી વેબસાઇટ ગૂગલ પર ઇન્ડેક્સ થઈ છે કે નહીં તે આનાથી ચેક થાય છે.</p>
        <div class="highlight-box">
          <strong>સરળ ભાષામાં કેવી રીતે મેળવવું:</strong><br>
          ૧. <a href="https://search.google.com/search-console" target="_blank">Google Search Console</a> માં લોગીન કરો.<br>
          ૨. નવું ડોમેન ઉમેરો (દા.ત. <code>https://example.com</code>).<br>
          ૩. એજન્સીના ઈમેઈલ આઈડી (દા.ત. <code>your-agency@gmail.com</code>) ને <strong>Delegated Owner</strong> અથવા <strong>Full Access User</strong> તરીકે એડ કરો.<br>
          ૪. પ્રોફાઈલમાં આ ઈમેઈલ નાખી <strong>"Auto-Verify GSC"</strong> બટન દબાવો. સેલેનિયમ આપોઆપ વેરિફિકેશન ટૅગ વર્ડપ્રેસ સાઇટ પર મૂકી દેશે!
        </div>
      </div>

      <hr>

      <!-- Google Analytics -->
      <div class="mb-4">
        <h5 class="text-success fw-bold"><i class="fas fa-chart-line me-2"></i>Google Analytics (GA4) Property ID</h5>
        <p><strong>આ શું છે?</strong> તમારી સાઇટ પર રોજ કેટલા લોકો ક્યાંથી આવે છે તેનું ટ્રેકિંગ આનાથી થાય છે.</p>
        <div class="highlight-box">
          <strong>સરળ ભાષામાં કોડ ક્યાંથી મેળવવો:</strong><br>
          ૧. <a href="https://analytics.google.com" target="_blank">Google Analytics</a> માં લોગીન કરો.<br>
          ૨. **Admin** (સેટિંગ આઈકોન) પર ક્લિક કરો.<br>
          ૩. **Data Streams** પર જાઓ અને તમારી વેબસાઇટ પર ક્લિક કરો.<br>
          ૪. ત્યાં તમને જમણી બાજુ ઉપર **Measurement ID** જોવા મળશે (જે `G-XXXXXXXXXX` ફોર્મેટમાં હશે).<br>
          ૫. આ આઈડી કોપી કરીને અહીં નાખો અને **"Auto-Install GA"** બટન દબાવો. સેલેનિયમ આપોઆપ ટ્રેકિંગ કોડ સાઇટના હેડરમાં સેટ કરી દેશે!
        </div>
      </div>

      <hr>

      <!-- Google Ads -->
      <div>
        <h5 class="text-warning fw-bold"><i class="fas fa-ad me-2"></i>Google Ads Conversion ID</h5>
        <p><strong>આ શું છે?</strong> ગૂગલ પર જાહેરાતો રન કરવા માટે અને સાઇટ પરથી કેટલા કોલ અથવા લીડ્સ મળ્યા તે ટ્રેક કરવા આ ટેગ જરૂરી છે.</p>
        <div class="highlight-box">
          <strong>સરળ ભાષામાં આઈડી કેવી રીતે મેળવવો:</strong><br>
          ૧. <a href="https://ads.google.com" target="_blank">Google Ads Account</a> ઓપન કરો.<br>
          ૨. **Tools and Settings** -> **Measurement** -> **Conversions** પર ક્લિક કરો.<br>
          ૩. નવી કન્વર્ઝન એક્શન સેટ કરો. છેલ્લે **Tag Setup** સેક્શનમાં જાઓ.<br>
          ૪. ત્યાં તમને **Conversion ID** જોવા મળશે (જે `AW-XXXXXXXXXX` ફોર્મેટમાં હશે).<br>
          ૫. આ કોપી કરી પ્રોફાઇલમાં પેસ્ટ કરો અને **"Auto-Install Ads Tag"** બટન દબાવો. સેલેનિયમ આપોઆપ આ ટ્રેકિંગ કોડ પણ વેબસાઈટના કોડમાં સેટ કરી દેશે!
        </div>
      </div>

    </div>
  </div>

  <!-- SECTION 4: CMS Admin Login Credentials -->
  <div class="guide-section">
    <div class="guide-header bg-danger">
      <i class="fas fa-lock fa-lg me-2"></i> ૩. વેબસાઇટ એડમિન લોગીન વિગતો (CMS Admin Login Credentials)
    </div>
    <div class="guide-body gu-text">
      <p>આપણી સિસ્ટમ ઓટોમેટિક ઓન-પેજ ફિક્સિસ, ગૂગલ વેરિફિકેશન, એનાલિટિક્સ અને સ્કીમા સેટ કરવા માટે ક્લાયન્ટની સાઇટના બેકએન્ડમાં લોગીન થાય છે. તેના માટે આ વિગતો ભરો:</p>
      <ul>
        <li><strong>Admin Login URL:</strong> ક્લાયન્ટની વર્ડપ્રેસ સાઇટનો લોગીન પાથ (દા.ત. <code>https://example.com/wp-admin</code>).</li>
        <li><strong>Admin Username / Email:</strong> વર્ડપ્રેસના એડમિન અકાઉન્ટનું યુઝરનેમ (દા.ત. <code>admin</code>).</li>
        <li><strong>Admin Password:</strong> તે અકાઉન્ટનો પાસવર્ડ. (આ વિગતો ડેટાબેઝમાં એન્ક્રિપ્ટેડ ફોર્મેટમાં સુરક્ષિત રીતે સચવાય છે).</li>
      </ul>
      <div class="alert alert-warning small">
        <i class="fas fa-exclamation-triangle me-1"></i> <strong>નોંધ:</strong> જો તમે આ વિગતો નહીં ભરો, તો સેલેનિયમ ઓટો-સેટઅપ કરી શકશે નહીં અને તમારે કોડ જાતે કોપી-પેસ્ટ કરવો પડશે.
      </div>
    </div>
  </div>

  <!-- SECTION 5: Competitor Websites -->
  <div class="guide-section">
    <div class="guide-header bg-success">
      <i class="fas fa-users fa-lg me-2"></i> ૪. હરીફ વેબસાઇટ્સ (Competitor Websites)
    </div>
    <div class="guide-body gu-text">
      <p><strong>આ શું છે?</strong> તમારા ક્લાયન્ટના ક્ષેત્રમાં અન્ય જે પણ લોકો ગૂગલમાં આગળ છે તેમની સાઇટનો અભ્યાસ કરવા માટે આ ખાનું છે.</p>
      <div class="highlight-box">
        <strong>કેવી રીતે ઉપયોગ કરવો:</strong><br>
        ૧. ગૂગલમાં તમારો કીવર્ડ સર્ચ કરો.<br>
        ૨. પ્રથમ પેજ પર આવતી ટોચની ૨-૩ વેબસાઇટ્સના ડોમેન નામ જુઓ.<br>
        ૩. તે સાઇટના નામ અલ્પવિરામ (comma `,`) થી જોડીને અહીં લખો.<br>
        * દાખલા તરીકે: <code>competitor1.com, competitor2.in, competitor3.org</code><br>
        ૪. પ્રોફાઇલ સેવ કરો. આપણી સિસ્ટમ આપોઆપ તેમના કીવર્ડ્સ, રેન્ક અને લોડિંગ સ્પીડની સરખામણી કરીને ડેટા આપશે!
      </div>
    </div>
  </div>

  <!-- SECTION 6: Local Business Schema -->
  <div class="guide-section">
    <div class="guide-header bg-warning text-dark">
      <i class="fas fa-map-marker-alt fa-lg me-2"></i> ૫. લોકલ એસઈઓ સ્કીમા વન-ક્લિક સેટઅપ (Local Business Schema)
    </div>
    <div class="guide-body gu-text">
      <p><strong>આ શું છે?</strong> ગૂગલ મેપ્સ અને ગૂગલ સર્ચમાં તમારા સ્થાનિક વિસ્તાર (દા.ત. રાજકોટ, અમદાવાદ) ના કસ્ટમર્સ માટે પ્રથમ પેજ પર લાવવા માટે આ લોકલ બિઝનેસ ડેટા (JSON-LD) જરૂરી છે.</p>
      <div class="highlight-box">
        <strong>સેટઅપ કઈ રીતે કરવું:</strong><br>
        ૧. સેક્શન ૨ માં ક્લાયન્ટની દુકાનનું સચોટ સરનામું (Business Address) અને સમય (Operating Hours) ભરો.<br>
        * સરનામાનું ઉદાહરણ: <code>101, Business Hub, Kalawad Road, Rajkot, Gujarat 360005</code><br>
        * સમયનું ઉદાહરણ: <code>Mo-Sa 09:00-19:00</code> (સોમવાર થી શનિવાર સવારે ૯ થી સાંજે ૭ સુધી)<br>
        ૨. પેજ સેવ કરો.<br>
        ૩. સેક્શન ૬ માં જઈને **"Auto-Install Local Business Schema"** બટન પર સિંગલ ક્લિક કરો.<br>
        ૪. સેલેનિયમ આપોઆપ તમારા ક્લાયન્ટનો લોકેશન સ્કીમા જનરેટ કરી વર્ડપ્રેસના કોડમાં ઇન્સ્ટોલ કરી દેશે!
      </div>
    </div>
  </div>

  <!-- SOCIAL ACCOUNTS MANAGEMENT -->
  <div class="guide-section">
    <div class="guide-header bg-info text-dark">
      <i class="fas fa-users-cog fa-lg me-2"></i> ૬. સોશિયલ મીડિયા એકાઉન્ટ્સ કેવી રીતે લિંક કરવા (Manage Social Accounts)
    </div>
    <div class="guide-body gu-text">
      <p>જ્યાં સોશિયલ મીડિયા પર આપણી ડેઇલી બેકલિન્ક્સ પોસ્ટ થાય છે તે એકાઉન્ટ્સ સેટઅપ કરવાની રીત:</p>
      
      <!-- Single Platform Multiple Accounts -->
      <div class="mb-3">
        <h6 class="fw-bold text-primary"><i class="fas fa-plus-circle me-1"></i>૧ પ્લેટફોર્મ માટે ૫ થી વધુ અકાઉન્ટ્સ ઉમેરવાની રીત:</h6>
        <p>જો તમારે એક જ ક્લાયન્ટ માટે ૫ અલગ Pinterest કે WordPress એકાઉન્ટ ઉમેરવા હોય તો:</p>
        <ol>
          <li>મેનુમાંથી **Submissions** પેજ પર જાઓ.</li>
          <li>જે પણ પ્લેટફોર્મ (દા.ત. Pinterest) પર જવું હોય ત્યાં **"Add Credentials"** અથવા **"Add More Account"** પર ક્લિક કરો.</li>
          <li>પ્રથમ એકાઉન્ટનું યુઝરનેમ અને પાસવર્ડ ભરી સેવ કરો.</li>
          <li>ફરીથી **"Add More Account"** પર ક્લિક કરો અને બીજા એકાઉન્ટનું યુઝરનેમ-પાસવર્ડ લખી સેવ કરો.</li>
          <li>આ રીતે તમે અગણિત અકાઉન્ટ્સ કન્ફિગર કરી શકો છો. સિસ્ટમ આપોઆપ બધા અકાઉન્ટ્સને વારાફરતી યુઝ કરીને રોજ પોસ્ટ કરશે!</li>
        </ol>
      </div>

      <hr>

      <!-- Bulk Upload -->
      <div>
        <h6 class="fw-bold text-success"><i class="fas fa-file-import me-1"></i>બધા અકાઉન્ટ્સ એકસાથે સેવ કરવા (Bulk Add):</h6>
        <p>જો બધી જ વિગતો ફોર્મ ભર્યા વિના એકસાથે એડ કરવી હોય તો:</p>
        <ol>
          <li>**Submissions** પેજ પર જઈને **"Bulk Add Accounts"** બટન પર ક્લિક કરો.</li>
          <li>નીચે આપેલા ફોર્મેટમાં ટેક્સ્ટ બોક્સમાં એકાઉન્ટ્સ લખો:<br>
          <code>પ્લેટફોર્મ,યુઝરનેમ,પાસવર્ડ</code></li>
          <li>ઉદાહરણ:<br>
          <pre class="bg-light p-2 rounded small">bluesky,user1.bsky.social,app-password-here
pinterest,email1@gmail.com,password123
pinterest,email2@gmail.com,password456</pre></li>
          <li>**"Add All Accounts"** દબાવો. બધા જ પ્લેટફોર્મના એકાઉન્ટ્સ એક સેકન્ડમાં સેવ થઈ જશે!</li>
        </ol>
      </div>

    </div>
  </div>

  <!-- HOW TO RUN SCHEDULER DAILY -->
  <div class="guide-section">
    <div class="guide-header bg-success">
      <i class="fas fa-clock fa-lg me-2"></i> ૭. ડેઇલી શિડ્યુલર કેવી રીતે ચલાવવું (How to Run Daily Scheduler)
    </div>
    <div class="guide-body gu-text">
      <p>રોજ ઓટો-આર્ટિકલ લખીને સાઇટ્સ પર ઓટોમેટિક સબમિટ કરવા માટે નીચેની પદ્ધતિ વાપરો:</p>
      <ul>
        <li><strong>રીત ૧ (મેન્યુઅલ રન):</strong> રોજ સવારે તમારા કમ્પ્યુટરમાં પ્રોજેક્ટ ફોલ્ડરમાં રહેલી **`run-scheduler.bat`** ફાઈલ પર ડબલ ક્લિક કરો. તે બધી સાઇટ પર પોસ્ટ કરવાનું ચાલુ કરી દેશે.</li>
        <li><strong>રીત ૨ (૧૦૦% ઓટોમેટિક વિન્ડોઝ સેટઅપ):</strong>
          <ol>
            <li>વિન્ડોઝમાં **Task Scheduler** ઓપન કરો.</li>
            <li>**Create Basic Task** પર ક્લિક કરો અને નામ આપો: <code>SEO Poster</code>.</li>
            <li>ટેસ્ક ટ્રિગરમાં **Daily** સિલેક્ટ કરીને રોજ સવારનો સમય સેટ કરો.</li>
            <li>એક્શનમાં **Start a program** પર ક્લિક કરીને **`run-auto-schedule.bat`** ફાઈલ સિલેક્ટ કરો.</li>
            <li>તે સક્સેસફુલી સેવ કરી લો. હવે તમારું કમ્પ્યુટર ચાલુ હશે ત્યારે તે રોજ સવારે બેકગ્રાઉન્ડમાં આપોઆપ રન થઈ જશે!</li>
          </ol>
        </li>
      </ul>
    </div>
  </div>

  <div class="text-center mt-4 mb-5">
    <a href="dashboard.php" class="btn btn-primary btn-lg me-3">
      <i class="fas fa-tachometer-alt me-2"></i>ગો ટુ ડેશબોર્ડ (Dashboard)
    </a>
    <a href="add-project.php" class="btn btn-success btn-lg">
      <i class="fas fa-plus me-2"></i>નવો પ્રોજેક્ટ ઉમેરો (Add Project)
    </a>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
