using System.Collections.Generic;
using Microsoft.AspNetCore.Mvc;
using Protobuild.Website.Models;

namespace Protobuild.Website.Controllers
{
    public class HomeController : Controller
    {
        [Route("/")]
        public IActionResult Index()
        {
            var model = new HomeModel();

            model.DetectedPlatform = "windows";

            model.Installers = new List<HomeInstallerModel>
            {
                new HomeInstallerModel
                {
                    Name = "Download for Windows",
                    Platform = "windows",
                    Url = $"{ProtobuildEnv.GetDomain()}/get/windows",
                    Command = null
                },
                new HomeInstallerModel
                {
                    Name = "Download for macOS",
                    Platform = "mac",
                    Url = null,
                    Command = $"curl -L {ProtobuildEnv.GetDomain()}/get/mac | bash"
                },
                new HomeInstallerModel
                {
                    Name = "Download for Linux",
                    Platform = "linux",
                    Url = null,
                    Command = $"curl -L {ProtobuildEnv.GetDomain()}/get/linux | bash"
                },
            };

            return View(model);
        }
    }
}
