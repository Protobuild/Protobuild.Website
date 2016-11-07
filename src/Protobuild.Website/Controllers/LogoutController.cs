using Microsoft.AspNetCore.Mvc;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Protobuild.Website.Controllers
{
    public class LogoutController : Controller
    {
        [Route("/logout")]
        public IActionResult Index()
        {
            return View();
        }
    }
}
