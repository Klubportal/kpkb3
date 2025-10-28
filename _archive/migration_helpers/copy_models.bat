@echo off
echo 🚀 COPYING MODELS FROM kp_club_management TO kpkb3
echo =================================================

set SOURCE=c:\xampp\htdocs\kp_club_management\app\Models
set TARGET=c:\xampp\htdocs\kpkb3\app\Models

echo Source: %SOURCE%
echo Target: %TARGET%
echo.

echo 📁 Creating required directories...
mkdir "%TARGET%\Core" 2>nul
mkdir "%TARGET%\Integration" 2>nul
mkdir "%TARGET%\Marketing" 2>nul
mkdir "%TARGET%\Platform" 2>nul
mkdir "%TARGET%\System" 2>nul

echo.
echo 📋 Copying Core Models...
xcopy "%SOURCE%\Core\Club.php" "%TARGET%\Core\" /Y
xcopy "%SOURCE%\Core\ClubBanner.php" "%TARGET%\Core\" /Y
xcopy "%SOURCE%\Core\ClubExtended.php" "%TARGET%\Core\" /Y
xcopy "%SOURCE%\Core\ClubMember.php" "%TARGET%\Core\" /Y
xcopy "%SOURCE%\Core\ClubSocialLink.php" "%TARGET%\Core\" /Y
xcopy "%SOURCE%\Core\ClubSponsor.php" "%TARGET%\Core\" /Y
xcopy "%SOURCE%\Core\SubscriptionPackage.php" "%TARGET%\Core\" /Y

echo.
echo 🔗 Copying Integration Models...
xcopy "%SOURCE%\Integration\CometClub.php" "%TARGET%\Integration\" /Y
xcopy "%SOURCE%\Integration\CometCompetition.php" "%TARGET%\Integration\" /Y
xcopy "%SOURCE%\Integration\CometPlayer.php" "%TARGET%\Integration\" /Y
xcopy "%SOURCE%\Integration\CometPlayerStat.php" "%TARGET%\Integration\" /Y
xcopy "%SOURCE%\Integration\CometSync.php" "%TARGET%\Integration\" /Y
xcopy "%SOURCE%\Integration\CometTeam.php" "%TARGET%\Integration\" /Y

echo.
echo 📢 Copying Marketing Models...
xcopy "%SOURCE%\Marketing\AdvertisingBanner.php" "%TARGET%\Marketing\" /Y
xcopy "%SOURCE%\Marketing\Banner.php" "%TARGET%\Marketing\" /Y
xcopy "%SOURCE%\Marketing\Sponsor.php" "%TARGET%\Marketing\" /Y
xcopy "%SOURCE%\Marketing\SponsorBanner.php" "%TARGET%\Marketing\" /Y
xcopy "%SOURCE%\Marketing\SponsorLogo.php" "%TARGET%\Marketing\" /Y

echo.
echo 🌐 Copying Platform Models...
xcopy "%SOURCE%\Platform\Club.php" "%TARGET%\Platform\" /Y
xcopy "%SOURCE%\Platform\Domain.php" "%TARGET%\Platform\" /Y
xcopy "%SOURCE%\Platform\EmailTemplate.php" "%TARGET%\Platform\" /Y
xcopy "%SOURCE%\Platform\PlatformSetting.php" "%TARGET%\Platform\" /Y
xcopy "%SOURCE%\Platform\PlatformUser.php" "%TARGET%\Platform\" /Y
xcopy "%SOURCE%\Platform\Subscription.php" "%TARGET%\Platform\" /Y
xcopy "%SOURCE%\Platform\SupportTicket.php" "%TARGET%\Platform\" /Y
xcopy "%SOURCE%\Platform\Tenant.php" "%TARGET%\Platform\" /Y

echo.
echo ⚙️ Copying System Models...
xcopy "%SOURCE%\System\ContactFormSubmission.php" "%TARGET%\System\" /Y
xcopy "%SOURCE%\System\EmailSetting.php" "%TARGET%\System\" /Y
xcopy "%SOURCE%\System\EmailTemplate.php" "%TARGET%\System\" /Y
xcopy "%SOURCE%\System\LanguageTranslation.php" "%TARGET%\System\" /Y
xcopy "%SOURCE%\System\SocialLink.php" "%TARGET%\System\" /Y
xcopy "%SOURCE%\System\WebsiteSetting.php" "%TARGET%\System\" /Y

echo.
echo ✅ Model copying completed!
echo.
echo 📝 NEXT STEPS:
echo 1. Check for namespace conflicts
echo 2. Review database migrations for new models
echo 3. Update Filament resources if needed
echo 4. Test the application
echo.
pause
