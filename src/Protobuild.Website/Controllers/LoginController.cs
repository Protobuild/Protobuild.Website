using Microsoft.AspNetCore.Mvc;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Authorization;

namespace Protobuild.Website.Controllers
{
    [Authorize]
    public class LoginController : Controller
    {
        [Route("/login")]
        public IActionResult Index()
        {
            return View();
        }
    }
}
