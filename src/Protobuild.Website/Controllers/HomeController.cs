﻿using System;
using System.Collections.Generic;
using Microsoft.AspNetCore.Diagnostics;
using Microsoft.AspNetCore.Mvc;
using Protobuild.Website.Exceptions;
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
                    Command = $"curl -L {ProtobuildEnv.GetDomain()}/get/linux | bash",
                    ShowDependencyWarning = true
                },
            };

            return View(model);
        }

        [Route("/error")]
        public IActionResult Error(Exception exception)
        {
            var exceptionHandlerFeature = HttpContext.Features.Get<IExceptionHandlerFeature>();
            var httpException = exceptionHandlerFeature.Error as HttpException;
            if (httpException != null)
            {
                HttpContext.Response.StatusCode = httpException.StatusCode;
            }

            return View(exceptionHandlerFeature.Error);
        }

        [Route("/error/{code}")]
        public IActionResult Error(Exception exception, int code)
        {
            return View(new HttpException(code));
        }
    }
}
