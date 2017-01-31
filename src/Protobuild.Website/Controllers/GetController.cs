using Microsoft.AspNetCore.Mvc;

namespace Protobuild.Website.Controllers
{
    public class GetController : Controller
    {
        [Route("/get")]
        [Route("/get/{platform}")]
        public IActionResult Index(string platform = null)
        {
            switch (platform)
            {
                case "windows":
                    return Redirect("https://github.com/Protobuild/Protobuild.Manager/releases/download/latest/ProtobuildWindowsInstall.exe");
                case "mac":
                    return Redirect("https://github.com/Protobuild/Protobuild.Manager/releases/download/latest/ProtobuildMacOSInstall.sh");
                case "linux":
                    return Redirect("https://github.com/Protobuild/Protobuild.Manager/releases/download/latest/ProtobuildLinuxInstall.sh");
                default:
                    return Redirect("https://github.com/Protobuild/Protobuild/raw/master/Protobuild.exe");
            }
        }
    }
}
