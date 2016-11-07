using Microsoft.AspNetCore.Mvc;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Authorization;
using Protobuild.Website.Authorization;

namespace Protobuild.Website.Controllers
{
    public class LoginController : Controller
    {
        [ProtobuildAuthorized]
        [Route("/login")]
        public IActionResult Index()
        {
            return View();
        }
    }
}
